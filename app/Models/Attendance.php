<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "student_id", "date", "status", "marked_by", "remarks"];

    protected $casts = ["date" => "date"];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, "marked_by");
    }
}
