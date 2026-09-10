<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSubjectTeacher;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * "Classes & Subjects": every section this teacher has a reason to
     * walk into, ordered with their own class-teacher (homeroom) section
     * first and clearly labelled — followed by every other class/subject
     * they teach.
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        $classTeacherSectionIds = $teacher->classTeacherSectionIds();

        $sections = $teacher->attachedSections()
            ->map(function (Section $section) use ($teacher, $classTeacherSectionIds) {
                $section->is_class_teacher = $classTeacherSectionIds->contains($section->id);
                $section->subjects_taught = $teacher->subjectsFor($section->school_class_id, $section->id);
                $section->pupil_count = $section->students()->count();

                return $section;
            })
            ->sortBy([
                fn ($s) => $s->is_class_teacher ? 0 : 1,
                fn ($s) => $s->schoolClass->name,
                fn ($s) => $s->name,
            ])
            ->values();

        return view("teacher.classes.index", compact("sections"));
    }

    /**
     * A single class/section: Details (roster + snapshot stats), Teachers
     * (who teaches what subject here), and Attendance (last 5 marked days
     * + a way to mark today's).
     */
    public function show(Section $section)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        $allowedSectionIds = $teacher->attachedSections()->pluck("id");
        abort_unless($allowedSectionIds->contains($section->id), 403, "This isn't one of your classes.");

        $section->load(["schoolClass", "classTeacher.user"]);
        $isClassTeacher = $teacher->classTeacherSectionIds()->contains($section->id);

        $students = $section->students()
            ->with(["user", "latestExamResult"])
            ->orderBy("admission_no")
            ->get()
            ->map(function (Student $student) {
                $student->attendance_rate = $this->attendanceRate($student->id);

                return $student;
            });

        // --- Details tab stats ---
        $averagePerformance = $students->pluck("latestExamResult")
            ->filter()
            ->map(fn ($r) => $r->percentage())
            ->avg();

        $transferredIn = $students->where("admitted_via_transfer", true)->count();
        $transferredOut = $section->students()->where("status", "transferred_out")->count();

        // --- Teachers tab: every subject taught here and who teaches it ---
        $subjectTeachers = ClassSubjectTeacher::where("school_class_id", $section->school_class_id)
            ->where(function ($q) use ($section) {
                $q->whereNull("section_id")->orWhere("section_id", $section->id);
            })
            ->with(["subject", "teacher.user"])
            ->get()
            ->sortBy(fn ($a) => $a->subject->name ?? "");

        // --- Attendance tab: last 5 distinct dates marked for this section ---
        $studentIds = $students->pluck("id");
        $recentDates = Attendance::whereIn("student_id", $studentIds)
            ->select("date")->distinct()->orderByDesc("date")->limit(5)->pluck("date");

        $attendanceGrid = Attendance::whereIn("student_id", $studentIds)
            ->whereIn("date", $recentDates)
            ->get()
            ->groupBy("student_id");

        return view("teacher.classes.show", compact(
            "section", "isClassTeacher", "students", "averagePerformance",
            "transferredIn", "transferredOut", "subjectTeachers", "recentDates", "attendanceGrid"
        ));
    }

    protected function attendanceRate(int $studentId): ?float
    {
        $total = Attendance::where("student_id", $studentId)->count();

        if ($total === 0) {
            return null;
        }

        $present = Attendance::where("student_id", $studentId)->whereIn("status", ["present", "late"])->count();

        return round(($present / $total) * 100, 1);
    }
}
