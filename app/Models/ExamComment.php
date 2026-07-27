<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamComment extends Model
{
    use HasFactory;

    protected $fillable = ["exam_id", "student_id", "class_teacher_comment", "principal_comment", "recorded_by"];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, "recorded_by");
    }
}
