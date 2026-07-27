<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id", "admission_no", "school_class_id", "section_id",
        "guardian_name", "guardian_phone", "dob", "address",
        "school_level", "pathway", "upi_number", "assessment_number",
    ];

    protected $casts = [
        "dob" => "date",
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

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function cbcRecords()
    {
        return $this->hasMany(CbcCompetencyRecord::class);
    }

    public function coreCompetencyRecords()
    {
        return $this->hasMany(CbcCoreCompetencyRecord::class);
    }

    public function sbaRecords()
    {
        return $this->hasMany(CbcSbaRecord::class);
    }

    public function portfolioItems()
    {
        return $this->hasMany(CbcPortfolioItem::class);
    }

    public function valueRecords()
    {
        return $this->hasMany(CbcValueRecord::class);
    }

    /**
     * Extra-curricular activities (e.g. swimming) this student has signed up for.
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class)->withPivot("signed_up_at")->withTimestamps();
    }

    public function inventoryIssues()
    {
        return $this->hasMany(InventoryIssue::class);
    }

    public function textbookLoans()
    {
        return $this->hasMany(TextbookLoan::class);
    }
    public function parents()
{
    return $this->belongsToMany(User::class, "parent_student", "student_id", "parent_id")
        ->withPivot("relationship")->withTimestamps();
}
}
