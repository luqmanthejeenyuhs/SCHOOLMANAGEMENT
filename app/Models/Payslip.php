<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_id", "month", "year", "basic_salary", "allowances_total", "gross_pay",
        "paye", "personal_relief", "shif", "nssf", "housing_levy", "other_deductions", "unpaid_leave_days",
        "total_deductions", "net_pay",
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function periodLabel(): string
    {
        return date("F", mktime(0, 0, 0, $this->month, 1)) . " " . $this->year;
    }
}
