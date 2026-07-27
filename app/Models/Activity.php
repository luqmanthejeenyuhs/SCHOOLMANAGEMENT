<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "description", "patron_id", "day_of_week", "start_time", "end_time", "venue",
    ];

    /**
     * The teacher in charge of the activity (e.g. the swimming coach).
     */
    public function patron()
    {
        return $this->belongsTo(Teacher::class, "patron_id");
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)->withPivot("signed_up_at")->withTimestamps();
    }

    /**
     * Whether the activity is scheduled to be happening right now, based on
     * its day and time window.
     */
    public function isHappeningNow(): bool
    {
        if (! $this->day_of_week || ! $this->start_time || ! $this->end_time) {
            return false;
        }

        $now = Carbon::now();

        if ($this->day_of_week !== "Daily" && $this->day_of_week !== $now->format("l")) {
            return false;
        }

        $start = $now->copy()->setTimeFromTimeString($this->start_time);
        $end = $now->copy()->setTimeFromTimeString($this->end_time);

        return $now->between($start, $end);
    }
}
