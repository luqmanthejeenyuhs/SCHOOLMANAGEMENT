<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected function teacherOrFail()
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        return $teacher;
    }

    public function index(Request $request)
    {
        $teacher = $this->teacherOrFail();
        $sections = $teacher->attachedSections();

        return view("teacher.reports.index", compact("sections"));
    }

    public function attendanceSummary(Request $request)
    {
        $teacher = $this->teacherOrFail();
        $sections = $teacher->attachedSections();

        $sectionId = $request->get("section_id");
        $section = $sectionId ? $sections->firstWhere("id", (int) $sectionId) : null;

        $from = $request->get("from", now()->startOfMonth()->toDateString());
        $to = $request->get("to", now()->toDateString());

        $rows = collect();

        if ($section) {
            $students = Student::with("user")->where("section_id", $section->id)->get();

            $attendance = Attendance::whereIn("student_id", $students->pluck("id"))
                ->whereBetween("date", [$from, $to])
                ->get()
                ->groupBy("student_id");

            $rows = $students->map(function ($student) use ($attendance) {
                $records = $attendance->get($student->id, collect());
                $total = $records->count();
                $present = $records->where("status", "present")->count();

                return (object) [
                    "student" => $student,
                    "present" => $present,
                    "absent" => $records->where("status", "absent")->count(),
                    "late" => $records->where("status", "late")->count(),
                    "excused" => $records->where("status", "excused")->count(),
                    "total_marked" => $total,
                    "rate" => $total > 0 ? round(($present / $total) * 100, 1) : null,
                ];
            })->sortBy(fn ($r) => $r->student->user->name)->values();
        }

        return view("teacher.reports.attendance", compact("sections", "section", "sectionId", "from", "to", "rows"));
    }

    public function examPerformance(Request $request)
    {
        $teacher = $this->teacherOrFail();
        $sections = $teacher->attachedSections();
        $myClassIds = $sections->pluck("schoolClass.id")->unique();

        $exams = Exam::with("schoolClass")->whereIn("school_class_id", $myClassIds)->get();
        $examId = $request->get("exam_id");
        $exam = $examId ? $exams->firstWhere("id", (int) $examId) : null;

        $rows = collect();
        $subjectColumns = collect();

        if ($exam) {
            $students = Student::with("user")->where("school_class_id", $exam->school_class_id)->get();

            $results = ExamResult::with("subject")
                ->where("exam_id", $exam->id)
                ->whereIn("student_id", $students->pluck("id"))
                ->get()
                ->groupBy("student_id");

            $subjectColumns = ExamResult::with("subject")->where("exam_id", $exam->id)
                ->get()->pluck("subject")->filter()->unique("id")->sortBy("name")->values();

            $rows = $students->map(function ($student) use ($results, $subjectColumns) {
                $studentResults = $results->get($student->id, collect())->keyBy("subject_id");

                $bySubject = $subjectColumns->mapWithKeys(function ($subject) use ($studentResults) {
                    $r = $studentResults->get($subject->id);

                    return [$subject->id => $r ? round($r->percentage(), 1) : null];
                });

                $recorded = $bySubject->filter(fn ($v) => $v !== null);

                return (object) [
                    "student" => $student,
                    "by_subject" => $bySubject,
                    "average" => $recorded->isEmpty() ? null : round($recorded->avg(), 1),
                    "subjects_recorded" => $recorded->count(),
                ];
            })
                // Ranked: highest average first, students with no marks yet sink to the bottom.
                ->sortByDesc(fn ($r) => $r->average ?? -1)
                ->values();

            $rank = 1;
            foreach ($rows as $row) {
                $row->rank = $row->average !== null ? $rank++ : null;
            }
        }

        return view("teacher.reports.exam_performance", compact("exams", "exam", "examId", "rows", "subjectColumns"));
    }
}
