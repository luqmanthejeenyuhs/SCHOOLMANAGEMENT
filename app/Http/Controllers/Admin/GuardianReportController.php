<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\GuardianReportLog;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Services\GuardianReportService;
use App\Support\Facades\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Communication > Parent Reports: send guardians their child's attendance,
 * exam results and/or fee balance by SMS and email — for one learner, a
 * stream, or a whole class.
 */
class GuardianReportController extends Controller
{
    public function __construct(protected GuardianReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy("name")->get();
        $sections = Section::with("schoolClass")->orderBy("school_class_id")->orderBy("name")->get();
        $exams = Exam::with("schoolClass")->orderByDesc("exam_date")->get();
        $logs = GuardianReportLog::with("student.user")->latest()->paginate(15);

        // "Send to guardians" buttons on the class / stream / student pages
        // link here with ?class= / ?section= / ?student= to pre-select the audience.
        $prefill = ["scope" => "class", "school_class_id" => null, "section_id" => null, "admission_no" => null];

        if ($request->filled("student") && $student = Student::find($request->input("student"))) {
            $prefill = array_merge($prefill, ["scope" => "student", "admission_no" => $student->admission_no]);
        } elseif ($request->filled("section") && $section = Section::find($request->input("section"))) {
            $prefill = array_merge($prefill, ["scope" => "section", "section_id" => $section->id]);
        } elseif ($request->filled("class") && $class = SchoolClass::find($request->input("class"))) {
            $prefill = array_merge($prefill, ["scope" => "class", "school_class_id" => $class->id]);
        }

        return view("admin.guardian_reports.index", compact("classes", "sections", "exams", "logs", "prefill"));
    }

    /**
     * Dry run: shows how many guardians would be reached and a sample SMS.
     * Redirects back (PRG) with the form input kept, so the admin can tweak and send.
     */
    public function preview(Request $request)
    {
        [$students, $opts, $data] = $this->resolve($request);

        $preview = $this->reports->preview($students, $opts, $data["note"] ?? null);

        return redirect()->route("admin.guardian_reports.index")
            ->withInput()
            ->with("preview", $preview);
    }

    public function send(Request $request)
    {
        [$students, $opts, $data] = $this->resolve($request);

        // A whole-class email run is synchronous; don't let PHP's default
        // 30s limit cut it off half way through.
        @set_time_limit(300);

        $stats = $this->reports->send($students, $opts, $data["channels"], $data["note"] ?? null, Auth::id());

        $bits = [];
        if (in_array("sms", $data["channels"], true)) {
            $bits[] = "SMS: {$stats['sms_sent']} sent"
                .($stats["sms_queued"] ? ", {$stats['sms_queued']} queued (no SMS key set)" : "")
                .($stats["sms_failed"] ? ", {$stats['sms_failed']} failed" : "");
        }
        if (in_array("email", $data["channels"], true)) {
            $bits[] = "Email: {$stats['email_sent']} sent".($stats["email_failed"] ? ", {$stats['email_failed']} failed" : "");
        }

        $message = "Reports for {$stats['students']} learner(s) — ".implode(" · ", $bits).".";
        if ($stats["no_contact"]) {
            $message .= " {$stats['no_contact']} learner(s) had no usable guardian contact for the chosen channel(s).";
        }

        return redirect()->route("admin.guardian_reports.index")->with("success", $message);
    }

    /**
     * Validate the form and turn it into [students, report options, validated data].
     */
    protected function resolve(Request $request): array
    {
        $tenantId = Tenant::id();
        $wantsResults = in_array("results", (array) $request->input("types", []), true);
        $wantsAttendance = in_array("attendance", (array) $request->input("types", []), true);

        $data = $request->validate([
            "scope" => ["required", "in:class,section,student"],
            "school_class_id" => [
                "required_if:scope,class", "nullable",
                Rule::exists("school_classes", "id")->where("school_id", $tenantId),
            ],
            "section_id" => [
                "required_if:scope,section", "nullable",
                Rule::exists("sections", "id")->where("school_id", $tenantId),
            ],
            "admission_no" => ["required_if:scope,student", "nullable", "string", "max:50"],
            "types" => ["required", "array", "min:1"],
            "types.*" => ["in:attendance,results,fees"],
            "exam_id" => [
                Rule::requiredIf($wantsResults), "nullable",
                Rule::exists("exams", "id")->where("school_id", $tenantId),
            ],
            "from" => [Rule::requiredIf($wantsAttendance), "nullable", "date"],
            "to" => [Rule::requiredIf($wantsAttendance), "nullable", "date", "after_or_equal:from"],
            "channels" => ["required", "array", "min:1"],
            "channels.*" => ["in:sms,email"],
            "note" => ["nullable", "string", "max:200"],
        ], [
            "types.required" => "Pick at least one thing to include in the report.",
            "channels.required" => "Pick SMS, email, or both.",
            "exam_id.required" => "Choose which exam's results to send.",
        ]);

        $query = Student::with(["user", "schoolClass", "section", "guardians"])
            ->where("status", "active");

        match ($data["scope"]) {
            "class" => $query->where("school_class_id", $data["school_class_id"]),
            "section" => $query->where("section_id", $data["section_id"]),
            "student" => $query->where("admission_no", trim($data["admission_no"])),
        };

        $students = $query->get()->sortBy(fn ($s) => $s->admission_no)->values();

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                $data["scope"] === "student" ? "admission_no" : "scope" => "No active learners found for that selection.",
            ]);
        }

        $opts = [
            "types" => $data["types"],
            "from" => Carbon::parse($data["from"] ?? now()->subDays(30))->startOfDay(),
            "to" => Carbon::parse($data["to"] ?? now())->endOfDay(),
            "exam" => $wantsResults ? Exam::find($data["exam_id"]) : null,
        ];

        return [$students, $opts, $data];
    }
}
