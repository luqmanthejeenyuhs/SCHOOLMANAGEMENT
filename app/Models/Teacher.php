<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "user_id", "employee_id", "qualification", "address", "joining_date"];

    protected $casts = [
        "joining_date" => "date",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function documents()
    {
        return $this->hasMany(TeacherDocument::class);
    }
}
