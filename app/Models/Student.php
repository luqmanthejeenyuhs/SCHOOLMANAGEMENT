<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "user_id", "admission_no", "school_class_id", "section_id",
        "guardian_name", "guardian_phone", "dob", "address",
        "school_level", "pathway", "upi_number", "assessment_number",
        "status", "admitted_via_transfer",
    ];

    protected $casts = [
        "dob" => "date",
        "admitted_via_transfer" => "boolean",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * The single most recent exam result recorded for this student, across
     * every subject — used for a "Last Exam Grade" column. Relies on
     * ExamResult's auto-increment id as a recency proxy (results are only
     * ever inserted/updated for the exam being marked right now).
     */
    public function latestExamResult()
    {
        return $this->hasOne(ExamResult::class)->latestOfMany();
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function cbcRecords()
    {
        return $this->hasMany(CbcCompetencyRecord::class);
    }

    /**
     * Extra-curricular activities (e.g. swimming) this student has signed up for.
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class)->withPivot("signed_up_at")->withTimestamps();
    }
}
