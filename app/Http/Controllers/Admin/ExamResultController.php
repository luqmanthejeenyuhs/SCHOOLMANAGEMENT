<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamComment;
use App\Models\Student;
use App\Services\ExamReportService;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function __construct(protected ExamReportService $reportService)
    {
    }

    /**
     * Class-wide results grid: every subject as a column, plus computed
     * total/mean/grade and class + stream position per student.
     */
    public function show(Exam $exam)
    {
        $exam->load("schoolClass");
        $report = $this->reportService->classResults($exam);

        return view("admin.results.show", [
            "exam" => $exam,
            "subjects" => $report["subjects"],
            "rows" => $report["students"],
        ]);
    }

    /**
     * Printable report card for a single student: subject breakdown, totals,
     * mean, grade, class/stream position, and an editable teacher comment.
     */
    public function reportCard(Exam $exam, Student $student)
    {
        $exam->load("schoolClass");
        $report = $this->reportService->classResults($exam);

        $row = collect($report["students"])->firstWhere(fn ($r) => $r["student"]->id === $student->id);

        if (! $row) {
            return back()->with("error", "This student has no results recorded for this exam yet.");
        }

        $student->load(["user", "schoolClass", "section"]);
        $comment = ExamComment::firstOrNew(["exam_id" => $exam->id, "student_id" => $student->id]);

        return view("admin.results.report_card", [
            "exam" => $exam,
            "student" => $student,
            "subjects" => $report["subjects"],
            "row" => $row,
            "classSize" => count($report["students"]),
            "comment" => $comment,
        ]);
    }

    /**
     * Save/update the class teacher and principal remarks shown on the report card.
     */
    public function storeComment(Request $request, Exam $exam, Student $student)
    {
        $data = $request->validate([
            "class_teacher_comment" => "nullable|string|max:1000",
            "principal_comment" => "nullable|string|max:1000",
        ]);

        ExamComment::updateOrCreate(
            ["exam_id" => $exam->id, "student_id" => $student->id],
            array_merge($data, ["recorded_by" => $request->user()->id])
        );

        return back()->with("success", "Comment saved.");
    }
}
