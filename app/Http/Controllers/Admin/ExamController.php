<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with("schoolClass")->latest()->get();
        $classes = SchoolClass::all();

        return view("admin.exams.index", compact("exams", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "school_class_id" => ["required", Rule::exists("school_classes", "id")->where("school_id", Tenant::id())],
            "term" => "nullable|string|max:100",
            "exam_date" => "nullable|date",
        ]);
        Exam::create($data);

        return back()->with("success", "Exam created.");
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with("success", "Exam deleted.");
    }
}
