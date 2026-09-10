<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Read-only profile for a pupil in one of this teacher's own classes —
     * academic info only (attendance history, exam results). No fee/guardian
     * financial info here; that stays admin-only (see Admin\StudentController).
     */
    public function show(Student $student)
    {
        $teacher = Auth::user()->teacher;
        abort_if(! $teacher, 403, "No staff record is linked to your account yet.");

        $allowedSectionIds = $teacher->attachedSections()->pluck("id");
        abort_unless($student->section_id && $allowedSectionIds->contains($student->section_id), 403, "This student isn't in one of your classes.");

        $student->load(["user", "schoolClass", "section"]);

        $examResults = $student->examResults()->with(["exam", "subject"])->latest("id")->limit(20)->get();
        $attendance = $student->attendances()->latest("date")->limit(30)->get();

        return view("teacher.students.show", compact("student", "examResults", "attendance"));
    }
}
