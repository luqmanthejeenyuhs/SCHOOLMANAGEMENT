<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Admin\TimetableController as AdminTimetableController;
use App\Http\Controllers\Controller;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_if(! $teacher, 404);

        $slots = TimetableSlot::with(["section.schoolClass", "subject"])
            ->where("teacher_id", $teacher->id)
            ->get();

        $timeRanges = $slots
            ->map(fn ($slot) => $slot->start_time.'|'.$slot->end_time)
            ->unique()
            ->sort()
            ->values();

        return view("teacher.timetable", [
            "slots" => $slots,
            "timeRanges" => $timeRanges,
            "days" => AdminTimetableController::DAYS,
        ]);
    }
}
