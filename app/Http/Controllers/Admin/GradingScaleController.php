<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradingScale;
use Illuminate\Http\Request;

class GradingScaleController extends Controller
{
    public function index()
    {
        $scales = GradingScale::orderByDesc("min_score")->get();

        return view("admin.grading_scales.index", compact("scales"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "grade" => "required|string|max:10",
            "min_score" => "required|numeric|min:0|max:100|lt:max_score",
            "max_score" => "required|numeric|min:0|max:100",
            "points" => "nullable|numeric|min:0",
            "remark" => "nullable|string|max:255",
        ]);

        GradingScale::create($data);

        return back()->with("success", "Grade band added.");
    }

    public function destroy(GradingScale $gradingScale)
    {
        $gradingScale->delete();

        return back()->with("success", "Grade band removed.");
    }
}
