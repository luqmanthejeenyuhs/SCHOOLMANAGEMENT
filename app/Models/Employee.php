<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id", "teacher_id", "name", "job_title", "is_teaching_staff",
        "id_number", "kra_pin", "nssf_number", "shif_number", "phone",
        "basic_salary", "house_allowance", "transport_allowance", "other_allowances",
        "employment_date", "is_active",
    ];

    protected $casts = [
        "employment_date" => "date",
        "is_teaching_staff" => "boolean",
        "is_active" => "boolean",
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function grossPay(): float
    {
        return (float) $this->basic_salary + $this->house_allowance + $this->transport_allowance + $this->other_allowances;
    }
}
