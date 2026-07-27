<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamComment;
use App\Models\Student;
use App\Services\ExamReportService;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Same data as reportCard(), rendered through a PDF-safe template and
     * streamed back as a downloadable file instead of an HTML page.
     */
    public function downloadReportCardPdf(Exam $exam, Student $student)
    {
        $exam->load("schoolClass");
        $report = $this->reportService->classResults($exam);

        $row = collect($report["students"])->firstWhere(fn ($r) => $r["student"]->id === $student->id);

        if (! $row) {
            return back()->with("error", "This student has no results recorded for this exam yet.");
        }

        $student->load(["user", "schoolClass", "section"]);
        $comment = ExamComment::firstOrNew(["exam_id" => $exam->id, "student_id" => $student->id]);

        $pdf = Pdf::loadView("admin.results.report_card_pdf", [
            "exam" => $exam,
            "student" => $student,
            "subjects" => $report["subjects"],
            "row" => $row,
            "classSize" => count($report["students"]),
            "comment" => $comment,
        ])->setPaper("a4");

        $filename = str($student->user->name)->slug()."-".str($exam->name)->slug()."-report-card.pdf";

        return $pdf->download($filename);
    }

    /**
     * Every student in the class, one report card per page, bundled into a
     * single PDF so the office can print/save a whole class in one go.
     */
    public function downloadClassReportCardsPdf(Exam $exam)
    {
        $exam->load("schoolClass");
        $report = $this->reportService->classResults($exam);

        $studentIds = collect($report["students"])->pluck("student.id");
        $comments = ExamComment::where("exam_id", $exam->id)
            ->whereIn("student_id", $studentIds)
            ->get()
            ->keyBy("student_id");

        $pdf = Pdf::loadView("admin.results.report_cards_bulk_pdf", [
            "exam" => $exam,
            "subjects" => $report["subjects"],
            "rows" => $report["students"],
            "classSize" => count($report["students"]),
            "comments" => $comments,
        ])->setPaper("a4");

        $filename = str($exam->schoolClass->name)->slug()."-".str($exam->name)->slug()."-report-cards.pdf";

        return $pdf->download($filename);
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
