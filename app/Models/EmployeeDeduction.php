<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "employee_id", "deduction_type_id", "amount", "is_active"];

    protected $casts = ["is_active" => "boolean"];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class);
    }
}
