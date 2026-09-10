<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "employee_id", "leave_type", "start_date", "end_date", "days",
        "reason", "status", "reviewed_by", "reviewed_at", "review_note",
    ];

    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
        "reviewed_at" => "datetime",
    ];

    public const TYPES = [
        "annual" => "Annual Leave",
        "sick" => "Sick Leave",
        "maternity" => "Maternity Leave",
        "paternity" => "Paternity Leave",
        "compassionate" => "Compassionate Leave",
        "unpaid" => "Unpaid Leave",
        "other" => "Other",
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
