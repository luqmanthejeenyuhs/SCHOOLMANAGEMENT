<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\GradingScale;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with("schoolClass")->get();
        $examId = $request->get("exam_id");
        $subjectId = $request->get("subject_id");

        $exam = $examId ? Exam::find($examId) : null;
        $subjects = $exam ? Subject::where("school_class_id", $exam->school_class_id)->get() : collect();
        $students = collect();

        if ($exam && $subjectId) {
            $students = Student::with(["user", "examResults" => function ($q) use ($examId, $subjectId) {
                $q->where("exam_id", $examId)->where("subject_id", $subjectId);
            }])->where("school_class_id", $exam->school_class_id)->get();
        }

        return view("teacher.results", compact("exams", "exam", "subjects", "students", "examId", "subjectId"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "exam_id" => "required|exists:exams,id",
            "subject_id" => "required|exists:subjects,id",
            "marks" => "required|array",
            // "lte:max_marks" enforces, server-side, that no score can exceed the
            // paper's max marks (e.g. can't enter 35/30) — this can't be trusted
            // to client-side input alone since the request can be replayed.
            "marks.*" => "nullable|numeric|min:0|lte:max_marks",
            "max_marks" => "required|numeric|min:1",
        ]);

        foreach ($data["marks"] as $studentId => $marks) {
            if ($marks === null || $marks === "") {
                continue;
            }
            $percentage = ($marks / $data["max_marks"]) * 100;
            $grade = GradingScale::forPercentage($percentage)?->grade;

            ExamResult::updateOrCreate(
                ["exam_id" => $data["exam_id"], "student_id" => $studentId, "subject_id" => $data["subject_id"]],
                ["marks_obtained" => $marks, "max_marks" => $data["max_marks"], "grade" => $grade]
            );
        }

        return back()->with("success", "Results saved.");
    }
}
