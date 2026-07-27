<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcValueRecord extends Model
{
    use HasFactory;

    protected $fillable = ["student_id", "value_area", "term", "rating", "remarks", "recorded_by"];

    public const VALUES = [
        "love" => "Love",
        "responsibility" => "Responsibility",
        "respect" => "Respect",
        "unity" => "Unity",
        "peace" => "Peace",
        "patriotism" => "Patriotism",
        "integrity" => "Integrity",
    ];

    public const RATINGS = [
        "EE" => "Exceeding Expectation",
        "ME" => "Meeting Expectation",
        "AE" => "Approaching Expectation",
        "BE" => "Below Expectation",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, "recorded_by");
    }

    public function valueLabel(): string
    {
        return self::VALUES[$this->value_area] ?? $this->value_area;
    }
}
