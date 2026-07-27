<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcCoreCompetencyRecord extends Model
{
    use HasFactory;

    protected $fillable = ["student_id", "competency", "term", "performance_level", "remarks", "recorded_by"];

    public const COMPETENCIES = [
        "communication_and_collaboration" => "Communication and Collaboration",
        "critical_thinking_and_problem_solving" => "Critical Thinking and Problem Solving",
        "creativity_and_imagination" => "Creativity and Imagination",
        "citizenship" => "Citizenship",
        "digital_literacy" => "Digital Literacy",
        "learning_to_learn" => "Learning to Learn",
        "self_efficacy" => "Self-Efficacy",
    ];

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

    public function recordedBy()
    {
        return $this->belongsTo(User::class, "recorded_by");
    }

    public function competencyLabel(): string
    {
        return self::COMPETENCIES[$this->competency] ?? $this->competency;
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->performance_level] ?? $this->performance_level;
    }
}
