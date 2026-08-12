<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\ExamResult;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with("schoolClass")->latest()->get();
        $classes = SchoolClass::all();

        return view("admin.subjects.index", compact("subjects", "classes"));
    }

    public function show(Subject $subject)
    {
        $subject->load("schoolClass");

        $assignments = ClassSubjectTeacher::where("subject_id", $subject->id)
            ->with(["teacher.user", "schoolClass", "section"])
            ->get();

        $results = ExamResult::where("subject_id", $subject->id)
            ->with("student.schoolClass")
            ->get()
            ->filter(fn ($r) => $r->student); // guard against orphaned rows

        $performanceByClass = $results
            ->groupBy(fn ($r) => $r->student->school_class_id)
            ->map(function ($group) {
                return [
                    "class" => $group->first()->student->schoolClass,
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
            "school_class_id" => ["required", Rule::exists("school_classes", "id")->where("school_id", Tenant::id())],
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
