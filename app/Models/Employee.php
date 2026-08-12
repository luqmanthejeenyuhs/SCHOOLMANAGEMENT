<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "user_id", "teacher_id", "staff_number", "name", "job_title", "is_teaching_staff",
        "id_number", "kra_pin", "nssf_number", "shif_number", "phone",
        "basic_salary", "house_allowance", "transport_allowance", "other_allowances",
        "employment_date", "is_active",
    ];

    protected $casts = [
        "employment_date" => "date",
        "is_teaching_staff" => "boolean",
        "is_active" => "boolean",
    ];

    /**
     * staff_number is never entered by hand — it's assigned automatically as
     * EMPL<id>, e.g. EMPL001. A unique placeholder is set before the row is
     * inserted (so the unique constraint doesn't collide across simultaneous
     * creates), then immediately corrected to the real id-based value once
     * the row exists and its id is known.
     */
    protected static function booted(): void
    {
        static::creating(function (self $employee) {
            if (empty($employee->staff_number)) {
                $employee->staff_number = "PENDING-".uniqid();
            }
        });

        static::created(function (self $employee) {
            if (str_starts_with((string) $employee->staff_number, "PENDING-")) {
                $employee->staff_number = "EMPL".str_pad((string) $employee->id, 3, "0", STR_PAD_LEFT);
                $employee->saveQuietly();
            }
        });
    }

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
