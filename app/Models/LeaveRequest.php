<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "employee_id", "leave_type", "start_date", "end_date", "reason", "status", "decided_by"];

    protected $casts = ["start_date" => "date", "end_date" => "date"];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, "decided_by");
    }

    public function days(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
