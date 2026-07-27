<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::with("sections")->get();
        $classId = $request->get("school_class_id");
        $sectionId = $request->get("section_id");
        $date = $request->get("date", now()->toDateString());

        $sections = collect();
        $students = collect();

        if ($classId) {
            $sections = SchoolClass::find($classId)?->sections ?? collect();

            $students = Student::with(["user", "attendances" => function ($q) use ($date) {
                $q->whereDate("date", $date);
            }])
                ->where("school_class_id", $classId)
                ->when($sectionId, fn ($q) => $q->where("section_id", $sectionId))
                ->join("users", "users.id", "=", "students.user_id")
                ->orderBy("users.name")
                ->select("students.*")
                ->get();
        }

        return view("teacher.attendance", compact("classes", "sections", "students", "classId", "sectionId", "date"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "date" => "required|date",
            "statuses" => "required|array",
            "statuses.*" => "in:present,absent,late,excused",
        ]);

        foreach ($data["statuses"] as $studentId => $status) {
            Attendance::updateOrCreate(
                ["student_id" => $studentId, "date" => $data["date"]],
                ["status" => $status, "marked_by" => Auth::id()]
            );
        }

        return back()->with("success", "Attendance saved for ".$data["date"]);
    }
}
