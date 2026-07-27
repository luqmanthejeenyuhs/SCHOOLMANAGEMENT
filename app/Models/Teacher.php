<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id", "employee_id", "id_number", "tsc_number", "qualification", "address",
        "joining_date", "next_of_kin_name", "next_of_kin_phone", "next_of_kin_relationship",
    ];

    protected $casts = [
        "joining_date" => "date",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function assignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    /**
     * Extra-curricular activities this teacher patrons/oversees (e.g. swimming coach).
     */
    public function activitiesAsPatron()
    {
        return $this->hasMany(Activity::class, "patron_id");
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }
}
