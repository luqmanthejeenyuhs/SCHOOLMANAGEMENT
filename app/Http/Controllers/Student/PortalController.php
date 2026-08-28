<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ClassSubjectTeacher;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TextbookLoan;
use App\Models\TimetableSlot;
use Illuminate\Support\Facades\Auth;

/**
 * A student's self-service view of their own record — one page per topic
 * so the sidebar can link straight to the part they want, rather than one
 * long scroll. Every method resolves the student from the logged-in user
 * (never from a route parameter), so a student can only ever see their own
 * data — there's no student_id in any of these URLs to tamper with.
 *
 * App\Http\Controllers\Admin\StudentController@show surfaces this same
 * information for admins looking at a specific student's profile.
 */
class PortalController extends Controller
{
    protected function currentStudent(array $with = []): Student
    {
        $student = Auth::user()->student()->with($with)->first();

        abort_if(! $student, 404, "No student profile is linked to your account. Contact your school admin.");

        return $student;
    }

    public function results()
    {
        $student = $this->currentStudent(["schoolClass", "section"]);

        $examResults = $student->examResults()
            ->with(["exam", "subject"])
            ->get()
            ->groupBy(fn ($r) => $r->exam->name ?? "Exam #".$r->exam_id);

        return view("student.results", compact("student", "examResults"));
    }

    public function fees()
    {
        $student = $this->currentStudent();

        $invoices = $student->feeInvoices()->with(["feeType", "payments"])->latest()->get();
        $totalBilled = $invoices->sum("amount");
        $totalPaid = $invoices->sum(fn ($inv) => $inv->totalPaid());
        $feeBalance = $invoices->sum(fn ($inv) => $inv->balance());
        $payments = Payment::whereIn("fee_invoice_id", $invoices->pluck("id"))
            ->with("invoice.feeType")
            ->latest("payment_date")
            ->get();

        return view("student.fees", compact("student", "invoices", "totalBilled", "totalPaid", "feeBalance", "payments"));
    }

    public function myClass()
    {
        $student = $this->currentStudent(["schoolClass", "section.classTeacher.user"]);

        $classmateCount = $student->section
            ? Student::where("section_id", $student->section_id)->where("id", "!=", $student->id)->count()
            : null;

        $timetable = $student->section
            ? TimetableSlot::where("section_id", $student->section_id)
                ->with(["subject", "teacher.user"])
                ->orderByRaw("FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->orderBy("start_time")
                ->get()
                ->groupBy("day_of_week")
            : collect();

        return view("student.class", compact("student", "classmateCount", "timetable"));
    }

    public function activities()
    {
        $student = $this->currentStudent();

        $myActivities = $student->activities()->with("patron.user")->get();
        $otherActivities = Activity::whereNotIn("id", $myActivities->pluck("id"))
            ->with("patron.user")
            ->orderBy("name")
            ->get();

        return view("student.activities", compact("student", "myActivities", "otherActivities"));
    }

    public function teachers()
    {
        $student = $this->currentStudent(["schoolClass", "section"]);

        $subjectTeachers = $student->schoolClass
            ? ClassSubjectTeacher::where("school_class_id", $student->school_class_id)
                ->where(fn ($q) => $q->where("section_id", $student->section_id)->orWhereNull("section_id"))
                ->with(["subject", "teacher.user"])
                ->get()
            : collect();

        return view("student.teachers", compact("student", "subjectTeachers"));
    }

    public function performance()
    {
        $student = $this->currentStudent();

        $results = $student->examResults()->with(["exam", "subject"])->get();

        // Average % per subject, across every exam sat — the headline
        // "how am I doing in each subject" view.
        $bySubject = $results->groupBy(fn ($r) => $r->subject->name ?? "Subject #".$r->subject_id)
            ->map(fn ($rows) => [
                "count" => $rows->count(),
                "average" => round($rows->avg(fn ($r) => $r->percentage()), 1),
                "best" => round($rows->max(fn ($r) => $r->percentage()), 1),
                "worst" => round($rows->min(fn ($r) => $r->percentage()), 1),
            ])
            ->sortKeys();

        // Average % per exam, in exam order — shows whether they're
        // trending up or down over time.
        $byExam = $results->groupBy(fn ($r) => $r->exam->name ?? "Exam #".$r->exam_id)
            ->map(fn ($rows) => round($rows->avg(fn ($r) => $r->percentage()), 1));

        $overallAverage = $results->isNotEmpty() ? round($results->avg(fn ($r) => $r->percentage()), 1) : null;

        return view("student.performance", compact("student", "bySubject", "byExam", "overallAverage"));
    }

    public function library()
    {
        $student = $this->currentStudent();

        $loans = TextbookLoan::where("student_id", $student->id)
            ->with(["copy.item"])
            ->latest("issued_at")
            ->get();

        $currentLoans = $loans->whereNull("returned_at");
        $pastLoans = $loans->whereNotNull("returned_at");

        return view("student.library", compact("student", "currentLoans", "pastLoans"));
    }
}
