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
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CbcController extends Controller
{
    /**
     * The CBC hub: 5 tabs covering learner profile flags, the learning-area
     * hierarchy, core competencies, SBA + portfolio, and values/behaviour.
     */
    public function index()
    {
        $learningAreas = CbcLearningArea::with("strands.subStrands")->latest()->get();
        $classes = SchoolClass::with("sections")->orderBy("name")->get();

        $students = Student::with(["user", "schoolClass", "section"])->orderBy("admission_no")->get();

        $recentCompetencies = CbcCoreCompetencyRecord::with("student.user")->latest()->limit(15)->get();
        $recentSba = CbcSbaRecord::with(["student.user", "learningArea"])->latest()->limit(15)->get();
        $recentValues = CbcValueRecord::with("student.user")->latest()->limit(15)->get();
        $portfolioItems = CbcPortfolioItem::with(["student.user", "subStrand"])->latest()->limit(20)->get();

        return view("admin.cbc.index", compact(
            "learningAreas", "classes", "students",
            "recentCompetencies", "recentSba", "recentValues", "portfolioItems"
        ));
    }

    // ---------------------------------------------------------------
    // Learning Areas & Strands (existing, quick-add via modal)
    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------
    // Learner Profile (UPI, pathway, school level)
    // ---------------------------------------------------------------

    public function updateLearnerProfile(Request $request, Student $student)
    {
        $data = $request->validate([
            "upi_number" => "nullable|string|max:50",
            "assessment_number" => "nullable|string|max:50",
            "school_level" => "required|in:junior,senior",
            "pathway" => "nullable|string|max:255",
        ]);

        $student->update($data);

        return back()->with("success", "Learner profile updated for {$student->user->name}.");
    }

    // ---------------------------------------------------------------
    // Core Competencies checklist (grid entry, one competency at a time)
    // ---------------------------------------------------------------

    public function coreCompetencyGrid(Request $request)
    {
        $classes = SchoolClass::with("sections")->get();
        $classId = $request->get("school_class_id");
        $sectionId = $request->get("section_id");
        $competency = $request->get("competency");
        $term = $request->get("term", "Term 1 ".now()->year);

        $students = collect();
        if ($classId && $competency) {
            $students = Student::with(["user", "coreCompetencyRecords" => fn ($q) => $q->where("term", $term)->where("competency", $competency)])
                ->where("school_class_id", $classId)
                ->when($sectionId, fn ($q) => $q->where("section_id", $sectionId))
                ->orderBy("admission_no")
                ->get();
        }

        return view("admin.cbc.core_competencies", compact("classes", "classId", "sectionId", "competency", "term", "students"));
    }

    public function storeCoreCompetencies(Request $request)
    {
        $data = $request->validate([
            "competency" => "required|in:".implode(",", array_keys(CbcCoreCompetencyRecord::COMPETENCIES)),
            "term" => "required|string|max:100",
            "levels" => "required|array",
        ]);

        foreach ($data["levels"] as $studentId => $level) {
            if (! $level) {
                continue;
            }
            CbcCoreCompetencyRecord::updateOrCreate(
                ["student_id" => $studentId, "competency" => $data["competency"], "term" => $data["term"]],
                ["performance_level" => $level, "recorded_by" => Auth::id()]
            );
        }

        return back()->with("success", "Core competency ratings saved.");
    }

    // ---------------------------------------------------------------
    // SBA — School-Based Assessment scores (grid entry)
    // ---------------------------------------------------------------

    public function sbaGrid(Request $request)
    {
        $classes = SchoolClass::with("sections")->get();
        $learningAreas = CbcLearningArea::orderBy("name")->get();
        $classId = $request->get("school_class_id");
        $sectionId = $request->get("section_id");
        $learningAreaId = $request->get("cbc_learning_area_id");
        $sbaNumber = $request->get("sba_number", 1);
        $term = $request->get("term", "Term 1 ".now()->year);

        $students = collect();
        if ($classId && $learningAreaId) {
            $students = Student::with(["user", "sbaRecords" => fn ($q) => $q->where("term", $term)
                ->where("cbc_learning_area_id", $learningAreaId)->where("sba_number", $sbaNumber)])
                ->where("school_class_id", $classId)
                ->when($sectionId, fn ($q) => $q->where("section_id", $sectionId))
                ->orderBy("admission_no")
                ->get();
        }

        return view("admin.cbc.sba", compact(
            "classes", "learningAreas", "classId", "sectionId", "learningAreaId", "sbaNumber", "term", "students"
        ));
    }

    public function storeSba(Request $request)
    {
        $data = $request->validate([
            "cbc_learning_area_id" => "required|exists:cbc_learning_areas,id",
            "sba_number" => "required|integer|min:1|max:3",
            "term" => "required|string|max:100",
            "max_score" => "required|numeric|min:1",
            "scores" => "required|array",
            "scores.*" => "nullable|numeric|min:0|lte:max_score",
        ]);

        foreach ($data["scores"] as $studentId => $score) {
            if ($score === null || $score === "") {
                continue;
            }
            CbcSbaRecord::updateOrCreate(
                [
                    "student_id" => $studentId, "cbc_learning_area_id" => $data["cbc_learning_area_id"],
                    "term" => $data["term"], "sba_number" => $data["sba_number"],
                ],
                ["score" => $score, "max_score" => $data["max_score"], "recorded_by" => Auth::id()]
            );
        }

        return back()->with("success", "SBA scores saved.");
    }

    // ---------------------------------------------------------------
    // Portfolio evidence (single-item upload via modal)
    // ---------------------------------------------------------------

    public function storePortfolio(Request $request)
    {
        $data = $request->validate([
            "student_id" => "required|exists:students,id",
            "cbc_sub_strand_id" => "nullable|exists:cbc_sub_strands,id",
            "title" => "required|string|max:255",
            "evidence_type" => "required|in:".implode(",", array_keys(CbcPortfolioItem::TYPES)),
            "term" => "nullable|string|max:100",
            "notes" => "nullable|string|max:1000",
            "file" => "required|file|mimes:pdf,jpg,jpeg,png,mp3,wav,mp4,mov|max:20480",
        ]);

        $path = $request->file("file")->store("cbc_portfolio/{$data['student_id']}");

        CbcPortfolioItem::create([
            "student_id" => $data["student_id"],
            "cbc_sub_strand_id" => $data["cbc_sub_strand_id"] ?? null,
            "title" => $data["title"],
            "evidence_type" => $data["evidence_type"],
            "file_path" => $path,
            "original_filename" => $request->file("file")->getClientOriginalName(),
            "term" => $data["term"] ?? null,
            "notes" => $data["notes"] ?? null,
            "uploaded_by" => Auth::id(),
        ]);

        return back()->with("success", "Portfolio evidence uploaded.");
    }

    public function downloadPortfolio(CbcPortfolioItem $item)
    {
        abort_unless(Storage::exists($item->file_path), 404);

        return Storage::download($item->file_path, $item->original_filename ?? $item->title);
    }

    public function destroyPortfolio(CbcPortfolioItem $item)
    {
        Storage::delete($item->file_path);
        $item->delete();

        return back()->with("success", "Portfolio item removed.");
    }

    // ---------------------------------------------------------------
    // Values & Behaviour tracker (grid entry, one value at a time)
    // ---------------------------------------------------------------

    public function valuesGrid(Request $request)
    {
        $classes = SchoolClass::with("sections")->get();
        $classId = $request->get("school_class_id");
        $sectionId = $request->get("section_id");
        $valueArea = $request->get("value_area");
        $term = $request->get("term", "Term 1 ".now()->year);

        $students = collect();
        if ($classId && $valueArea) {
            $students = Student::with(["user", "valueRecords" => fn ($q) => $q->where("term", $term)->where("value_area", $valueArea)])
                ->where("school_class_id", $classId)
                ->when($sectionId, fn ($q) => $q->where("section_id", $sectionId))
                ->orderBy("admission_no")
                ->get();
        }

        return view("admin.cbc.values", compact("classes", "classId", "sectionId", "valueArea", "term", "students"));
    }

    public function storeValues(Request $request)
    {
        $data = $request->validate([
            "value_area" => "required|in:".implode(",", array_keys(CbcValueRecord::VALUES)),
            "term" => "required|string|max:100",
            "ratings" => "required|array",
        ]);

        foreach ($data["ratings"] as $studentId => $rating) {
            if (! $rating) {
                continue;
            }
            CbcValueRecord::updateOrCreate(
                ["student_id" => $studentId, "value_area" => $data["value_area"], "term" => $data["term"]],
                ["rating" => $rating, "recorded_by" => Auth::id()]
            );
        }

        return back()->with("success", "Values & behaviour ratings saved.");
    }

    // ---------------------------------------------------------------
    // Descriptive Assessment Sheet — the full printable CBC report
    // ---------------------------------------------------------------

    public function report(Student $student, Request $request)
    {
        $term = $request->get("term", "Term 1 ".now()->year);

        $student->load(["user", "schoolClass", "section"]);

        $records = $student->cbcRecords()
            ->with("subStrand.strand.learningArea")
            ->where("term", $term)
            ->get()
            ->groupBy(fn ($r) => $r->subStrand->strand->learningArea->name);

        $coreCompetencies = $student->coreCompetencyRecords()->where("term", $term)->get()->keyBy("competency");
        $values = $student->valueRecords()->where("term", $term)->get()->keyBy("value_area");
        $sbaRecords = $student->sbaRecords()->with("learningArea")->where("term", $term)->orderBy("sba_number")->get()
            ->groupBy(fn ($r) => $r->learningArea->name ?? "General");
        $portfolioItems = $student->portfolioItems()->where("term", $term)->latest()->get();

        return view("admin.cbc.report", compact(
            "student", "records", "term", "coreCompetencies", "values", "sbaRecords", "portfolioItems"
        ));
    }
}
