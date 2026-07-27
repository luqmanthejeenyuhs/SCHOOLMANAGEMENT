<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = ["exam_id", "student_id", "subject_id", "marks_obtained", "max_marks", "grade"];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function percentage(): float
    {
        return $this->max_marks > 0 ? round(($this->marks_obtained / $this->max_marks) * 100, 1) : 0;
    }
}
