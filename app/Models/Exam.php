<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ["name", "school_class_id", "term", "exam_date"];

    protected $casts = ["exam_date" => "date"];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function comments()
    {
        return $this->hasMany(ExamComment::class);
    }
}
