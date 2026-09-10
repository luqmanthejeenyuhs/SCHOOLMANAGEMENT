<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\TimetableSlot;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher()->with("assignments.schoolClass", "assignments.section", "assignments.subject")->first();

        $sections = $teacher ? $teacher->attachedSections() : collect();

        // The class this teacher is personally responsible for, as
        // distinct from classes they simply teach a subject to — shown
        // first and marked "My Class" on the dashboard.
        $mainSectionIds = $teacher ? $sections->filter(fn ($s) => $s->class_teacher_id === $teacher->id)->pluck("id") : collect();

        $classSummaries = collect();
        $totalStudents = 0;
        $allPercentages = collect();

        foreach ($sections as $section) {
            $studentIds = Student::where("section_id", $section->id)->pluck("id");
            $totalStudents += $studentIds->count();

            $percentages = ExamResult::whereIn("student_id", $studentIds)->get()->map(fn ($r) => $r->percentage());
            $allPercentages = $allPercentages->merge($percentages);

            $classSummaries->push((object) [
                "section" => $section,
                "is_main" => $mainSectionIds->contains($section->id),
                "student_count" => $studentIds->count(),
                "average" => $percentages->isNotEmpty() ? round($percentages->avg(), 1) : null,
            ]);
        }

        // "My Class" first, then everything else alphabetically by class name.
        $classSummaries = $classSummaries->sortBy([
            fn ($a, $b) => $b->is_main <=> $a->is_main,
            fn ($a, $b) => ($a->section->schoolClass->name ?? "") <=> ($b->section->schoolClass->name ?? ""),
        ])->values();

        $subjectCount = $teacher
            ? \App\Models\ClassSubjectTeacher::where("teacher_id", $teacher->id)->distinct("subject_id")->count("subject_id")
            : 0;

        $overallAverage = $allPercentages->isNotEmpty() ? round($allPercentages->avg(), 1) : null;

        // Today's schedule at a glance — lessons, breaks/lunch, and any
        // personal slot (duty, prep, club, etc) for whichever weekday it
        // is right now.
        $todayName = now()->format("l");
        $todaySlots = collect();
        if ($teacher) {
            $todaySlots = TimetableSlot::with(["subject", "section.schoolClass"])
                ->where("day_of_week", $todayName)
                ->where(function ($q) use ($teacher) {
                    $q->where("teacher_id", $teacher->id)
                        ->orWhereIn("slot_type", TimetableSlot::SCHOOL_WIDE_TYPES);
                })
                ->orderBy("start_time")
                ->get();
        }

        return view("teacher.dashboard", compact(
            "teacher", "sections", "classSummaries", "totalStudents", "subjectCount",
            "overallAverage", "todaySlots", "todayName"
        ));
    }
}
