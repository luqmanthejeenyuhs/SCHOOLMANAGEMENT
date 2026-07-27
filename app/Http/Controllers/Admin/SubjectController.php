<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\ExamResult;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with("schoolClass")->latest()->get();
        $classes = SchoolClass::all();

        return view("admin.subjects.index", compact("subjects", "classes"));
    }

    /**
     * Everything about one subject: which teachers teach it (and in which
     * class/stream), plus how students are performing in it, broken down by
     * class so a head of department can see where extra support is needed.
     */
    public function show(Subject $subject)
    {
        $subject->load("schoolClass");

        $assignments = ClassSubjectTeacher::where("subject_id", $subject->id)
            ->with(["teacher.user", "schoolClass", "section"])
            ->get();

        $results = ExamResult::where("subject_id", $subject->id)
            ->with(["exam.schoolClass", "student"])
            ->get();

        $performanceByClass = $results
            ->filter(fn ($result) => $result->exam?->schoolClass)
            ->groupBy(fn ($result) => $result->exam->school_class_id)
            ->map(function ($group) {
                return [
                    "class" => $group->first()->exam->schoolClass,
                    "average" => round($group->avg(fn ($r) => $r->percentage()), 1),
                    "students_assessed" => $group->pluck("student_id")->unique()->count(),
                    "results_recorded" => $group->count(),
                ];
            })
            ->values();

        return view("admin.subjects.show", compact("subject", "assignments", "performanceByClass"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "school_class_id" => "required|exists:school_classes,id",
            "name" => "required|string|max:255",
            "code" => "nullable|string|max:50",
        ]);
        Subject::create($data);

        return back()->with("success", "Subject created.");
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with("success", "Subject deleted.");
    }
}
