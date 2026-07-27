<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcCompetencyRecord extends Model
{
    use HasFactory;

    protected $fillable = ["student_id", "cbc_sub_strand_id", "term", "performance_level", "remarks", "recorded_by"];

    public const LEVELS = [
        "EE" => "Exceeding Expectation",
        "ME" => "Meeting Expectation",
        "AE" => "Approaching Expectation",
        "BE" => "Below Expectation",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subStrand()
    {
        return $this->belongsTo(CbcSubStrand::class, "cbc_sub_strand_id");
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, "recorded_by");
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->performance_level] ?? $this->performance_level;
    }
}
