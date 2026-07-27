<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * The "Classes" tab: every stream (e.g. Grade 9 Green) with its class
     * teacher and student count, searchable by class, stream, or teacher name.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::withCount(["students", "sections", "subjects"])->orderBy("name")->get();
        $teachers = Teacher::with("user")->get();

        $search = $request->get("q");

        $sections = Section::with(["schoolClass", "classTeacher.user"])
            ->withCount("students")
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhereHas("schoolClass", fn ($q2) => $q2->where("name", "like", "%{$search}%"))
                        ->orWhereHas("classTeacher.user", fn ($q2) => $q2->where("name", "like", "%{$search}%"));
                });
            })
            ->orderBy("school_class_id")
            ->orderBy("name")
            ->get();

        return view("admin.classes.index", compact("classes", "sections", "teachers", "search"));
    }

    /**
     * Drill into a class: shows its streams (sections), each with a student count,
     * plus any students not yet assigned to a stream.
     */
    public function show(SchoolClass $class)
    {
        $class->loadCount("students");
        $sections = $class->sections()->withCount("students")->orderBy("name")->get();
        $unassignedCount = $class->students()->whereNull("section_id")->count();

        return view("admin.classes.show", compact("class", "sections", "unassignedCount"));
    }

    public function store(Request $request)
    {
        $data = $request->validate(["name" => "required|string|max:255|unique:school_classes,name"]);
        SchoolClass::create($data);

        return back()->with("success", "Class created.");
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate(["name" => "required|string|max:255|unique:school_classes,name,".$class->id]);
        $class->update($data);

        return back()->with("success", "Class updated.");
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return back()->with("success", "Class deleted.");
    }
}
