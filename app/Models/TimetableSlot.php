<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        "section_id", "subject_id", "teacher_id", "day_of_week", "start_time", "end_time", "room",
    ];

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
