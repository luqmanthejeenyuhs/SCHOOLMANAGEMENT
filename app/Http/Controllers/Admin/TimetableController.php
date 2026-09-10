<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public const DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    /**
     * Weekly timetable grid for one stream at a time, plus the form to add a
     * lesson (or free period / duty / club / break, etc — see
     * TimetableSlot::SLOT_TYPES) to it. Room/teacher/class clashes are all
     * blocked in store().
     */
    public function index(Request $request)
    {
        $sections = Section::with("schoolClass")->orderBy("school_class_id")->orderBy("name")->get();

        $sectionId = (int) $request->get("section_id", optional($sections->first())->id);
        $section = $sections->firstWhere("id", $sectionId);

        $slots = $section
            ? TimetableSlot::with(["subject", "teacher.user"])
                ->where(function ($q) use ($section) {
                    $q->where("section_id", $section->id)
                        ->orWhereIn("slot_type", TimetableSlot::SCHOOL_WIDE_TYPES);
                })
                ->get()
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

        // For non-lesson personal slots (free/duty/prep/club/sports), any
        // teacher in the school can be assigned — not just ones teaching
        // this particular class.
        $allTeachers = Teacher::with("user")->get()->sortBy(fn ($t) => $t->user->name ?? "");

        return view("admin.timetable.index", [
            "sections" => $sections,
            "section" => $section,
            "sectionId" => $sectionId,
            "slots" => $slots,
            "timeRanges" => $timeRanges,
            "subjects" => $subjects,
            "assignments" => $assignments,
            "allTeachers" => $allTeachers,
            "slotTypes" => TimetableSlot::SLOT_TYPES,
            "days" => self::DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "slot_type" => "required|in:".implode(",", array_keys(TimetableSlot::SLOT_TYPES)),
            "viewing_section_id" => "required|exists:sections,id",
            "section_id" => "nullable|exists:sections,id",
            "subject_id" => "nullable|exists:subjects,id",
            "teacher_id" => "nullable|exists:teachers,id",
            "label" => "nullable|string|max:255",
            "day_of_week" => "required|string|in:".implode(",", self::DAYS),
            "start_time" => "required|date_format:H:i",
            "end_time" => "required|date_format:H:i|after:start_time",
            "room" => "nullable|string|max:255",
        ]);

        $viewingSectionId = $data["viewing_section_id"];
        unset($data["viewing_section_id"]);

        $isSchoolWide = in_array($data["slot_type"], TimetableSlot::SCHOOL_WIDE_TYPES);
        $isLesson = $data["slot_type"] === "lesson";

        if ($isLesson) {
            $request->validate([
                "section_id" => "required|exists:sections,id",
                "subject_id" => "required|exists:subjects,id",
                "teacher_id" => "required|exists:teachers,id",
            ]);
        } elseif (! $isSchoolWide) {
            $request->validate(["teacher_id" => "required|exists:teachers,id"]);
        }

        // School-wide break/lunch blocks apply to everyone, not one class —
        // section_id/teacher_id are deliberately left blank for these.
        if ($isSchoolWide) {
            $data["section_id"] = null;
            $data["teacher_id"] = null;
        }
        if (! $isLesson) {
            $data["subject_id"] = null;
        }
        if ($isSchoolWide || $isLesson) {
            // label isn't used for lessons (displayLabel() builds it from
            // subject+class) or break/lunch (SLOT_TYPES label is enough).
            $data["label"] = $isLesson ? null : (($data["label"] ?? null) ?: null);
        }

        if (! empty($data["section_id"])) {
            if (TimetableSlot::where("section_id", $data["section_id"])
                ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
                ->exists()) {
                return back()->withInput()->withErrors(["start_time" => "This class already has something scheduled at an overlapping time on {$data['day_of_week']}."]);
            }
        }

        if (! empty($data["teacher_id"])) {
            if (TimetableSlot::where("teacher_id", $data["teacher_id"])
                ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
                ->exists()) {
                return back()->withInput()->withErrors(["teacher_id" => "This teacher already has something scheduled at an overlapping time on {$data['day_of_week']}."]);
            }
        }

        if (! empty($data["room"])) {
            if (TimetableSlot::where("room", $data["room"])
                ->overlapping($data["day_of_week"], $data["start_time"], $data["end_time"])
                ->exists()) {
                return back()->withInput()->withErrors(["room" => "Room \"{$data['room']}\" is already booked at an overlapping time on {$data['day_of_week']}."]);
            }
        }

        $data["school_id"] = Tenant::id();

        TimetableSlot::create($data);

        return redirect()->route("admin.timetable.index", ["section_id" => $viewingSectionId])
            ->with("success", "Timetable updated.");
    }

    public function destroy(TimetableSlot $timetableSlot)
    {
        $sectionId = $timetableSlot->section_id;
        $timetableSlot->delete();

        return redirect()->route("admin.timetable.index", ["section_id" => $sectionId])
            ->with("success", "Removed from the timetable.");
    }
}
