<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public const DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    /**
     * Weekly timetable grid for one stream at a time, plus the form to add a
     * lesson to it. Room/teacher/class clashes are all blocked in store().
     */
    public function index(Request $request)
    {
        $sections = Section::with("schoolClass")->orderBy("school_class_id")->orderBy("name")->get();

        $sectionId = (int) $request->get("section_id", optional($sections->first())->id);
        $section = $sections->firstWhere("id", $sectionId);

        $slots = $section
            ? TimetableSlot::with(["subject", "teacher.user"])->where("section_id", $section->id)->get()
            : collect();

        $timeRanges = $slots
            ->map(fn ($slot) => $slot->start_time.'|'.$slot->end_time)
            ->unique()
            ->sort()
            ->values();

        $subjects = $section ? Subject::where("school_class_id", $section->school_class_id)->orderBy("name")->get() : collect();

        // Only offer teachers who are actually assigned to teach this class (and,
        // where set, this specific stream), so the dropdown can't produce a
        // nonsensical timetable entry.
        $assignments = $section
            ? ClassSubjectTeacher::with(["teacher.user", "subject"])
                ->where("school_class_id", $section->school_class_id)
                ->where(function ($q) use ($section) {
                    $q->whereNull("section_id")->orWhere("section_id", $section->id);
                })
                ->get()
            : collect();

        return view("admin.timetable.index", [
            "sections" => $sections,
            "section" => $section,
            "sectionId" => $sectionId,
            "slots" => $slots,
            "timeRanges" => $timeRanges,
            "subjects" => $subjects,
            "assignments" => $assignments,
            "days" => self::DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "section_id" => "required|exists:sections,id",
            "subject_id" => "required|exists:subjects,id",
            "teacher_id" => "required|exists:teachers,id",
            "day_of_week" => "required|string|in:".implode(",", self::DAYS),
            "start_time" => "required|date_format:H:i",
            "end_time" => "required|date_format:H:i|after:start_time",
            "room" => "nullable|string|max:255",
        ]);

        if (TimetableSlot::where("section_id", $data["section_id"])
            ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
            ->exists()) {
            return back()->withInput()->withErrors(["start_time" => "This class already has a lesson scheduled at an overlapping time on {$data['day_of_week']}."]);
        }

        if (TimetableSlot::where("teacher_id", $data["teacher_id"])
            ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
            ->exists()) {
            return back()->withInput()->withErrors(["teacher_id" => "This teacher is already teaching another class at an overlapping time on {$data['day_of_week']}."]);
        }

        if (! empty($data["room"])) {
            if (TimetableSlot::where("room", $data["room"])
                ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
                ->exists()) {
                return back()->withInput()->withErrors(["room" => "Room \"{$data['room']}\" is already booked at an overlapping time on {$data['day_of_week']}."]);
            }
        }

        TimetableSlot::create($data);

        return redirect()->route("admin.timetable.index", ["section_id" => $data["section_id"]])
            ->with("success", "Lesson added to the timetable.");
    }

    public function destroy(TimetableSlot $timetableSlot)
    {
        $sectionId = $timetableSlot->section_id;
        $timetableSlot->delete();

        return redirect()->route("admin.timetable.index", ["section_id" => $sectionId])
            ->with("success", "Lesson removed.");
    }
}
