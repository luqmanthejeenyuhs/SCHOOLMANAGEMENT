<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbcCoreCompetencyRecord;
use App\Models\CbcLearningArea;
use App\Models\CbcPortfolioItem;
use App\Models\CbcSbaRecord;
use App\Models\CbcStrand;
use App\Models\CbcSubStrand;
use App\Models\CbcValueRecord;
use App\Models\Student;
use Illuminate\Http\Request;

class CbcController extends Controller
{
    public function index()
    {
        $learningAreas = CbcLearningArea::with("strands.subStrands")->latest()->get();
        $students = Student::with("user")->get();

        $recentCompetencies = CbcCoreCompetencyRecord::with("student.user")->latest()->take(20)->get();
        $recentValues = CbcValueRecord::with("student.user")->latest()->take(20)->get();
        $recentSba = CbcSbaRecord::with(["student.user", "learningArea"])->latest()->take(20)->get();
        $portfolioItems = CbcPortfolioItem::with(["student.user", "subStrand"])->latest()->take(20)->get();

        return view("admin.cbc.index", compact(
            "learningAreas",
            "students",
            "recentCompetencies",
            "recentValues",
            "recentSba",
            "portfolioItems"
        ));
    }

    public function storeLearningArea(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "school_level" => "required|in:junior,senior",
            "pathway" => "nullable|string|max:255",
        ]);
        CbcLearningArea::create($data);

        return back()->with("success", "Learning area created.");
    }

    public function destroyLearningArea(CbcLearningArea $learningArea)
    {
        $learningArea->delete();

        return back()->with("success", "Learning area deleted.");
    }

    public function storeStrand(Request $request)
    {
        $data = $request->validate([
            "cbc_learning_area_id" => "required|exists:cbc_learning_areas,id",
            "name" => "required|string|max:255",
        ]);
        CbcStrand::create($data);

        return back()->with("success", "Strand added.");
    }

    public function destroyStrand(CbcStrand $strand)
    {
        $strand->delete();

        return back()->with("success", "Strand deleted.");
    }

    public function storeSubStrand(Request $request)
    {
        $data = $request->validate([
            "cbc_strand_id" => "required|exists:cbc_strands,id",
            "name" => "required|string|max:255",
        ]);
        CbcSubStrand::create($data);

        return back()->with("success", "Sub-strand added.");
    }

    public function destroySubStrand(CbcSubStrand $subStrand)
    {
        $subStrand->delete();

        return back()->with("success", "Sub-strand deleted.");
    }

    /**
     * Printable MoE-style CBC learner progress report / summative assessment profile.
     */
    public function report(Student $student, Request $request)
    {
        $term = $request->get("term", "Term 1 ".now()->year);

        $student->load(["user", "schoolClass", "section"]);

        $records = $student->cbcRecords()
            ->with("subStrand.strand.learningArea")
            ->where("term", $term)
            ->get()
            ->groupBy(fn ($r) => $r->subStrand->strand->learningArea->name);

        return view("admin.cbc.report", compact("student", "records", "term"));
    }
}
