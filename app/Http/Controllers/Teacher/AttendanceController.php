<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        // Restricted to this teacher's own classes only — previously any
        // class in the school was selectable here regardless of whether the
        // teacher actually taught it.
        $sections = $teacher->attachedSections();
        $classes = $sections->pluck("schoolClass")->unique("id")->sortBy("name")->values();

        $classId = $request->get("school_class_id");
        $sectionId = $request->get("section_id");
        $date = $request->get("date", now()->toDateString());
        // Defaults to whichever half of the day it currently is, so a
        // teacher marking the register right after morning assembly (or
        // right after lunch) lands on the correct session without having
        // to think about it — but either can still be picked explicitly.
        $session = $request->get("session", now()->hour < 12 ? "morning" : "afternoon");

        $classSections = $classId ? $sections->where("school_class_id", $classId)->values() : collect();
        $students = collect();

        // Guard against a teacher fiddling with the URL to reach a class
        // that isn't theirs — the dropdown already only shows their own
        // classes, but the query string is still user input.
        $allowedSectionIds = $sections->pluck("id");
        $sectionAllowed = ! $sectionId || $allowedSectionIds->contains((int) $sectionId);

        if ($classId && $classes->pluck("id")->contains((int) $classId) && $sectionAllowed) {
            $students = Student::with(["user", "attendances" => function ($q) use ($date, $session) {
                $q->whereDate("date", $date)->where("session", $session);
            }])
                ->where("school_class_id", $classId)
                ->when($sectionId, fn ($q) => $q->where("section_id", $sectionId))
                ->join("users", "users.id", "=", "students.user_id")
                ->orderBy("users.name")
                ->select("students.*")
                ->get();
        } else {
            $classId = null;
        }

        return view("teacher.attendance", compact("classes", "classSections", "students", "classId", "sectionId", "date", "session"))
            ->with("sections", $classSections);
    }

    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403);

        $data = $request->validate([
            "date" => "required|date",
            "session" => "required|in:morning,afternoon",
            "statuses" => "required|array",
            "statuses.*" => "in:present,absent,late,excused",
            "reasons" => "nullable|array",
            "reasons.*" => "nullable|string|max:255",
        ]);

        // Only ever write attendance for students actually in one of this
        // teacher's own classes — the form only renders their own students,
        // but the POST body is still user input.
        $allowedStudentIds = Student::whereIn("section_id", $teacher->attachedSections()->pluck("id"))->pluck("id");

        foreach ($data["statuses"] as $studentId => $status) {
            if (! $allowedStudentIds->contains((int) $studentId)) {
                continue;
            }

            $reason = $data["reasons"][$studentId] ?? null;

            Attendance::updateOrCreate(
                ["student_id" => $studentId, "date" => $data["date"], "session" => $data["session"]],
                [
                    "status" => $status,
                    "marked_by" => Auth::id(),
                    // Present pupils don't need a reason; clear any stale one
                    // left over from a previous day's mark for the same slot.
                    "remarks" => $status === "present" ? null : $reason,
                ]
            );
        }

        return back()->with("success", ucfirst($data["session"])." attendance saved for ".$data["date"]);
    }
}
