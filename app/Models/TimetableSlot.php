<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "section_id", "subject_id", "teacher_id", "slot_type", "label",
        "day_of_week", "start_time", "end_time", "room",
    ];

    /**
     * "lesson" needs section_id/subject_id/teacher_id all set. Every other
     * type is either personal to one teacher (free/duty/prep/club/sports —
     * teacher_id set, section/subject null) or school-wide (break/lunch —
     * teacher_id also null, shown on every teacher's timetable).
     */
    public const SLOT_TYPES = [
        "lesson" => "Lesson",
        "free" => "Free Period",
        "duty" => "Duty / Admin",
        "prep" => "Marking / Prep",
        "club" => "Club / Society",
        "sports" => "Sports / Games",
        "break" => "Break Time",
        "lunch" => "Lunch Break",
        "other" => "Other",
    ];

    public const SCHOOL_WIDE_TYPES = ["break", "lunch"];

    /**
     * What to actually print in a timetable cell — the subject+class for a
     * lesson, or the type's own label (customised via $label if set, e.g.
     * "Basketball Club" instead of just "Club / Society").
     */
    public function displayLabel(): string
    {
        if ($this->slot_type === "lesson") {
            $subject = $this->subject->name ?? "Lesson";
            $class = $this->section?->schoolClass?->name;

            return $class ? "{$subject} ({$class})" : $subject;
        }

        return $this->label ?: (self::SLOT_TYPES[$this->slot_type] ?? "Other");
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Whether this lesson overlaps a given day/time range — used to detect
     * scheduling clashes (same class, same teacher, or same room double-booked).
     */
    public function scopeOverlapping($query, string $dayOfWeek, string $startTime, string $endTime)
    {
        return $query->where("day_of_week", $dayOfWeek)
            ->where("start_time", "<", $endTime)
            ->where("end_time", ">", $startTime);
    }
}
