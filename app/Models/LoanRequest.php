<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "employee_id", "request_type", "amount", "reason",
        "status", "reviewed_by", "reviewed_at", "review_note", "disbursed_at",
    ];

    protected $casts = [
        "amount" => "float",
        "reviewed_at" => "datetime",
        "disbursed_at" => "datetime",
    ];

    public const TYPES = [
        "loan" => "Staff Loan",
        "salary_advance" => "Salary Advance",
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, "reviewed_by");
    }
}
