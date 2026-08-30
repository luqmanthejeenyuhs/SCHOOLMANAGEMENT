<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\GradingScale;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        $myClassIds = $teacher->attachedSections()->pluck("schoolClass.id")->unique();

        // Restricted to exams for classes this teacher is actually attached
        // to — previously every exam in the whole school was listed here.
        $exams = Exam::with("schoolClass")->whereIn("school_class_id", $myClassIds)->get();

        // Optional preselect from a dashboard "Results" link for a specific class.
        $preselectClassId = $request->get("school_class_id");

        $examId = $request->get("exam_id");
        $subjectId = $request->get("subject_id");

        $exam = $examId ? $exams->firstWhere("id", (int) $examId) : null;

        // Restricted to subjects this teacher actually teaches in that
        // exam's class — previously every subject offered in the class was
        // selectable, letting a teacher enter marks for subjects they don't
        // teach.
        $subjects = $exam ? $teacher->subjectsFor($exam->school_class_id) : collect();
        $subject = $subjectId ? $subjects->firstWhere("id", (int) $subjectId) : null;

        $students = collect();

        if ($exam && $subject) {
            $students = Student::with(["user", "examResults" => function ($q) use ($examId, $subjectId) {
                $q->where("exam_id", $examId)->where("subject_id", $subjectId);
            }])->where("school_class_id", $exam->school_class_id)->get();

            // Running average across every subject recorded for this exam so
            // far (not just the one being entered right now) — shown as a
            // reference column so the teacher can see each pupil's overall
            // standing while entering marks, per the "system calculates the
            // average" requirement.
            $allResultsForExam = ExamResult::where("exam_id", $examId)
                ->whereIn("student_id", $students->pluck("id"))
                ->get()
                ->groupBy("student_id");

            foreach ($students as $student) {
                $rows = $allResultsForExam->get($student->id, collect());
                $student->running_average = $rows->isEmpty()
                    ? null
                    : round($rows->avg(fn ($r) => $r->percentage()), 1);
                $student->subjects_recorded = $rows->count();
            }
        }

        return view("teacher.results", compact("exams", "exam", "subjects", "subject", "students", "examId", "subjectId", "preselectClassId"));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403);

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

        $exam = Exam::findOrFail($data["exam_id"]);

        // Only allow saving for a subject this teacher is actually assigned
        // to teach in this exam's class — the dropdown already restricts
        // this, but the POST body is still user input.
        $allowedSubjectIds = $teacher->subjectsFor($exam->school_class_id)->pluck("id");
        abort_unless($allowedSubjectIds->contains((int) $data["subject_id"]), 403, "You don't teach this subject in this class.");

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
