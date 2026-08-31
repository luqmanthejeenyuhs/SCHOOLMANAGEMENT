<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "name", "is_statutory", "is_percentage", "rate_or_amount", "is_active"];

    protected $casts = ["is_statutory" => "boolean", "is_percentage" => "boolean", "is_active" => "boolean"];

    public function employeeDeductions()
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    /**
     * The four core Kenya statutory items — shown for reference on the
     * Statutory Deductions screen, but never manually assigned to an
     * employee: PayrollService computes these directly from gross pay every
     * time a payslip is generated, so there is nothing to configure here.
     */
    public static function seedStatutoryReference(School $school): void
    {
        $rows = [
            ["name" => "PAYE (Income Tax)", "is_statutory" => true],
            ["name" => "NSSF (Tier I & II)", "is_statutory" => true],
            ["name" => "SHIF", "is_statutory" => true],
            ["name" => "Affordable Housing Levy", "is_statutory" => true],
        ];

        foreach ($rows as $row) {
            self::allSchools()->firstOrCreate(
                ["school_id" => $school->id, "name" => $row["name"]],
                $row
            );
        }
    }
}
