<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\GradingScale;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $section = Section::with("schoolClass")->findOrFail($request->query("section_id"));
        $schoolClassId = $section->school_class_id;

        $exams = Exam::where("school_class_id", $schoolClassId)->orderByDesc("exam_date")->get();
        $examId = $request->query("exam_id");

        $students = Student::with("user")->where("section_id", $section->id)->get();
        $studentIds = $students->pluck("id");

        $results = ExamResult::with(["subject", "exam"])
            ->whereIn("student_id", $studentIds)
            ->when($examId, fn ($q) => $q->where("exam_id", $examId))
            ->get();

        // Subject-wise class average — where the class is doing well or
        // struggling as a whole, not any one student.
        $bySubject = $results->groupBy("subject_id")->map(function ($group) {
            $percentages = $group->map->percentage();

            return [
                "subject" => $group->first()->subject,
                "average" => round($percentages->avg(), 1),
                "entries" => $group->count(),
            ];
        })->sortByDesc("average")->values();

        // Per-student ranking — each student's average across every subject
        // they have a result for in the selected period.
        $byStudent = $results->groupBy("student_id")->map(function ($group) use ($students) {
            $student = $students->firstWhere("id", $group->first()->student_id);
            $average = round($group->map->percentage()->avg(), 1);

            return [
                "student" => $student,
                "average" => $average,
                "grade" => GradingScale::forPercentage($average)?->grade,
                "subjects_count" => $group->count(),
            ];
        })->sortByDesc("average")->values();

        // Students with zero results yet still deserve a row, so a teacher
        // can see who hasn't been marked at all rather than assuming
        // they're just not shown.
        $reportedStudentIds = $byStudent->pluck("student.id");
        $unreported = $students->whereNotIn("id", $reportedStudentIds);

        return view("teacher.reports.index", compact(
            "section", "exams", "examId", "bySubject", "byStudent", "unreported"
        ));
    }
}
