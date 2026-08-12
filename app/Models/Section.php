<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "school_class_id", "name", "class_teacher_id"];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * The teacher responsible for this stream (e.g. "Grade 9 Green" class teacher).
     */
    public function classTeacher()
    {
        return $this->belongsTo(Teacher::class, "class_teacher_id");
    }
}
