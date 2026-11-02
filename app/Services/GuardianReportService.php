<?php

namespace App\Services;

use App\Mail\StudentReportMail;
use App\Models\Exam;
use App\Models\GuardianReportLog;
use App\Models\Student;
use App\Support\Facades\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Builds a learner's report (attendance, exam results, fee balance) and
 * delivers it to their parents/guardians by SMS and/or email.
 *
 * Guardian contacts come from two places, merged and de-duplicated:
 *   1. parent portal accounts linked via the parent_student pivot (email + phone)
 *   2. the plain guardian_name / guardian_phone columns on the student
 */
class GuardianReportService
{
    /** @var array<int, array> exam_id => ExamReportService::classResults() output */
    protected array $examBoards = [];

    public function __construct(
        protected ExamReportService $examReports,
        protected SmsService $sms,
    ) {
    }

    // ------------------------------------------------------------------
    // Contacts
    // ------------------------------------------------------------------

    /**
     * @return Collection<int, array{name:string,email:?string,phone:?string,relationship:?string}>
     */
    public function contactsFor(Student $student): Collection
    {
        $student->loadMissing("guardians");

        $contacts = collect();

        foreach ($student->guardians as $guardian) {
            $contacts->push([
                "name" => $guardian->name,
                "email" => $guardian->email ?: null,
                "phone" => $this->normalizePhone($guardian->phone),
                "relationship" => $guardian->pivot->relationship ?? null,
            ]);
        }

        $legacyPhone = $this->normalizePhone($student->guardian_phone);
        if ($legacyPhone && ! $contacts->contains(fn ($c) => $c["phone"] === $legacyPhone)) {
            $contacts->push([
                "name" => $student->guardian_name ?: "Parent/Guardian",
                "email" => null,
                "phone" => $legacyPhone,
                "relationship" => null,
            ]);
        }

        return $contacts->values();
    }

    /**
     * Kenyan-friendly normalisation to +254XXXXXXXXX (what Africa's Talking
     * expects). Returns null for anything that doesn't look like a number.
     */
    public function normalizePhone(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $hadPlus = str_starts_with(trim($raw), "+");
        $n = preg_replace("/\D+/", "", $raw);

        return match (true) {
            str_starts_with($n, "254") && strlen($n) === 12 => "+".$n,
            str_starts_with($n, "0") && strlen($n) === 10 => "+254".substr($n, 1),
            strlen($n) === 9 && in_array($n[0], ["7", "1"], true) => "+254".$n,
            $hadPlus && strlen($n) >= 10 => "+".$n,
            default => null,
        };
    }

    // ------------------------------------------------------------------
    // Report content
    // ------------------------------------------------------------------

    /**
     * @param  array{types:array,from:Carbon,to:Carbon,exam:?Exam}  $opts
     */
    public function build(Student $student, array $opts): array
    {
        $student->loadMissing(["user", "schoolClass", "section"]);

        $types = $opts["types"];

        return [
            "student_id" => $student->id,
            "student_name" => $student->user->name ?? "Student",
            "admission_no" => $student->admission_no,
            "class_label" => trim(($student->schoolClass->name ?? "")." ".($student->section->name ?? "")),
            "generated_at" => now()->format("j M Y, H:i"),
            "attendance" => in_array("attendance", $types, true)
                ? $this->attendanceSummary($student, $opts["from"], $opts["to"])
                : null,
            "results_requested" => in_array("results", $types, true) && $opts["exam"] !== null,
            "results" => in_array("results", $types, true) && $opts["exam"]
                ? $this->examSummary($student, $opts["exam"])
                : null,
            "fees" => in_array("fees", $types, true) ? $this->feeSummary($student) : null,
        ];
    }

    public function attendanceSummary(Student $student, Carbon $from, Carbon $to): array
    {
        $rows = $student->attendances()
            ->whereBetween("date", [$from->toDateString(), $to->toDateString()])
            ->orderBy("date")
            ->get();

        $counts = $rows->countBy("status");
        $total = $rows->count();
        $present = (int) ($counts["present"] ?? 0);
        $late = (int) ($counts["late"] ?? 0);
        $absent = (int) ($counts["absent"] ?? 0);

        $absentDates = $rows->where("status", "absent")
            ->map(fn ($r) => $r->date->format("D j M"))
            ->unique()
            ->values();

        return [
            "range" => $from->format("j M")." - ".$to->format("j M Y"),
            "total" => $total,
            "present" => $present,
            "late" => $late,
            "absent" => $absent,
            "excused" => (int) ($counts["excused"] ?? 0),
            // Same definition the parent portal uses: present + late = attended.
            "rate" => $total > 0 ? round((($present + $late) / $total) * 100, 1) : null,
            "absent_dates" => $absentDates->take(8)->all(),
        ];
    }

    /**
     * Null when the student has no results for that exam.
     */
    public function examSummary(Student $student, Exam $exam): ?array
    {
        // Ranking needs the whole class's marks, so compute the board once
        // per exam and reuse it for every student in a bulk send.
        $board = $this->examBoards[$exam->id] ??= $this->examReports->classResults($exam);

        $row = $board["students"]->first(fn ($r) => $r["student"]->id === $student->id);

        if (! $row) {
            return null;
        }

        return [
            "exam" => $exam->name,
            "term" => $exam->term,
            "subjects" => $row["results"]->map(fn ($r) => [
                "subject" => $r->subject->name ?? "—",
                "marks" => (float) $r->marks_obtained,
                "max" => (float) $r->max_marks,
                "percentage" => $r->percentage(),
                "grade" => $r->grade,
            ])->sortBy("subject")->values()->all(),
            "total" => (float) $row["total"],
            "possible" => (float) $row["possible"],
            "mean" => $row["mean"],
            "grade" => $row["grade"],
            "class_position" => $row["class_position"],
            "stream_position" => $row["stream_position"] ?? null,
            "class_size" => $board["students"]->count(),
        ];
    }

    public function feeSummary(Student $student): array
    {
        $invoices = $student->feeInvoices()->with("payments")->get();

        return [
            "billed" => (float) $invoices->sum("amount"),
            "paid" => (float) $invoices->sum(fn ($inv) => $inv->totalPaid()),
            "balance" => (float) $invoices->sum(fn ($inv) => $inv->balance()),
        ];
    }

    /**
     * Compact SMS version of a report (one or two SMS segments).
     */
    public function smsText(array $report, string $schoolName, ?string $note = null): string
    {
        $head = "{$schoolName}: report for {$report['student_name']}";
        if ($report["class_label"] !== "") {
            $head .= " ({$report['class_label']})";
        }
        $parts = [$head.". "];

        if ($a = $report["attendance"]) {
            $parts[] = $a["total"] === 0
                ? "Attendance {$a['range']}: none recorded."
                : "Attendance {$a['range']}: {$a['rate']}% ({$a['absent']} absent, {$a['late']} late).";
        }

        if ($report["results_requested"]) {
            if ($r = $report["results"]) {
                $parts[] = "{$r['exam']}: mean {$r['mean']}%".($r["grade"] ? " ({$r['grade']})" : "")
                    .", position {$r['class_position']}/{$r['class_size']}.";
            } else {
                $parts[] = "No exam results recorded yet.";
            }
        }

        if ($f = $report["fees"]) {
            $parts[] = $f["balance"] > 0
                ? "Fee balance: KES ".number_format($f["balance"], 0)."."
                : "Fees fully paid.";
        }

        if ($note) {
            $parts[] = $note;
        }

        return Str::limit(trim(implode(" ", $parts)), 450, "…");
    }

    // ------------------------------------------------------------------
    // Preview + delivery
    // ------------------------------------------------------------------

    /**
     * Cheap dry-run for the "Preview" button: who would be reached, and what
     * the first learner's SMS would say. Sends nothing, logs nothing.
     */
    public function preview(Collection $students, array $opts, ?string $note): array
    {
        $schoolName = Tenant::current()?->name ?? "School";

        $withSms = $withEmail = $noContact = 0;
        foreach ($students as $student) {
            $contacts = $this->contactsFor($student);
            if ($contacts->isEmpty()) {
                $noContact++;

                continue;
            }
            if ($contacts->contains(fn ($c) => $c["phone"])) {
                $withSms++;
            }
            if ($contacts->contains(fn ($c) => $c["email"])) {
                $withEmail++;
            }
        }

        $sample = $students->first();

        return [
            "students" => $students->count(),
            "with_sms" => $withSms,
            "with_email" => $withEmail,
            "no_contact" => $noContact,
            "sample_name" => $sample->user->name ?? "",
            "sample_sms" => $this->smsText($this->build($sample, $opts), $schoolName, $note),
        ];
    }

    /**
     * @param  array<int,string>  $channels  any of "sms", "email"
     */
    public function send(Collection $students, array $opts, array $channels, ?string $note, ?int $sentBy): array
    {
        $school = Tenant::current();
        abort_if(! $school, 403, "No school context.");

        $typesLabel = implode(",", $opts["types"]);
        $smsKeyConfigured = (bool) config("services.africastalking.api_key");

        $stats = [
            "students" => $students->count(), "no_contact" => 0,
            "sms_sent" => 0, "sms_queued" => 0, "sms_failed" => 0,
            "email_sent" => 0, "email_failed" => 0,
        ];

        foreach ($students as $student) {
            $contacts = $this->contactsFor($student);

            if ($contacts->isEmpty()) {
                $stats["no_contact"]++;

                continue;
            }

            $report = $this->build($student, $opts);
            $smsText = in_array("sms", $channels, true) ? $this->smsText($report, $school->name, $note) : null;
            $reached = false;

            foreach ($contacts as $contact) {
                if ($smsText && $contact["phone"]) {
                    $reached = true;
                    try {
                        $ok = $this->sms->send($contact["phone"], $smsText, "report", $student->id, $sentBy);
                        $status = $ok ? "sent" : ($smsKeyConfigured ? "failed" : "queued");
                        $error = $status === "queued" ? "No AT_API_KEY set — logged but not transmitted." : null;
                    } catch (Throwable $e) {
                        $status = "failed";
                        $error = $e->getMessage();
                    }

                    $stats["sms_{$status}"]++;
                    $this->log($student, "sms", $contact["phone"], $contact["name"], $typesLabel, Str::limit($smsText, 120), $status, $error, $sentBy);
                }

                if (in_array("email", $channels, true) && $contact["email"]) {
                    $reached = true;
                    $mail = new StudentReportMail($school, $report, $contact["name"], $note);
                    try {
                        Mail::to($contact["email"])->send($mail);
                        $status = "sent";
                        $error = null;
                    } catch (Throwable $e) {
                        $status = "failed";
                        $error = $e->getMessage();
                    }

                    $stats["email_{$status}"]++;
                    $this->log($student, "email", $contact["email"], $contact["name"], $typesLabel, "Progress report for {$report['student_name']}", $status, $error, $sentBy);
                }
            }

            // Has contacts, but none usable on the channels chosen
            // (e.g. email-only send, guardian has only a phone number).
            if (! $reached) {
                $stats["no_contact"]++;
            }
        }

        return $stats;
    }

    protected function log(Student $student, string $channel, string $recipient, ?string $name, string $types, ?string $subject, string $status, ?string $error, ?int $sentBy): void
    {
        GuardianReportLog::create([
            "student_id" => $student->id,
            "channel" => $channel,
            "recipient" => $recipient,
            "recipient_name" => $name,
            "report_types" => $types,
            "subject" => $subject,
            "status" => $status,
            "error" => $error ? Str::limit($error, 500) : null,
            "sent_by" => $sentBy,
        ]);
    }
}
