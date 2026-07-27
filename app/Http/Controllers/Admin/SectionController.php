<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with(["schoolClass", "classTeacher.user"])->latest()->get();
        $classes = SchoolClass::all();

        return view("admin.sections.index", compact("sections", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "school_class_id" => "required|exists:school_classes,id",
            "name" => "required|string|max:255",
            "class_teacher_id" => "nullable|exists:teachers,id",
        ]);
        Section::create($data);

        return back()->with("success", "Stream created.");
    }

    /**
     * Assign or change the class teacher for a stream.
     */
    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            "class_teacher_id" => "nullable|exists:teachers,id",
        ]);
        $section->update($data);

        return back()->with("success", "Class teacher updated.");
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return back()->with("success", "Stream deleted.");
    }

    /**
     * Drill into a stream: shows its student roster with a quick fee-balance
     * and exam-average snapshot for each learner.
     */
    public function show(Section $section)
    {
        $section->load(["schoolClass", "classTeacher.user"]);

        $students = $section->students()
            ->with(["user", "feeInvoices.payments", "examResults"])
            ->orderBy("admission_no")
            ->get()
            ->map(function ($student) {
                $student->fee_balance = $student->feeInvoices->sum(fn ($inv) => $inv->balance());
                $student->exam_average = $student->examResults->count()
                    ? round($student->examResults->avg(fn ($r) => $r->percentage()), 1)
                    : null;

                return $student;
            });

        return view("admin.sections.show", compact("section", "students"));
    }
}
