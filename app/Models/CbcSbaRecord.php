<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcSbaRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        "student_id", "cbc_learning_area_id", "term", "sba_number",
        "score", "max_score", "remarks", "recorded_by",
    ];

    protected $casts = [
        "score" => "float",
        "max_score" => "float",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function learningArea()
    {
        return $this->belongsTo(CbcLearningArea::class, "cbc_learning_area_id");
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, "recorded_by");
    }

    public function percentage(): float
    {
        return $this->max_score > 0 ? round(($this->score / $this->max_score) * 100, 1) : 0.0;
    }
}
