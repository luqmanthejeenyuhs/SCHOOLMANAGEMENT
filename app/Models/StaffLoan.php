<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLoan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "employee_id", "loan_type", "principal", "interest_rate",
        "repayment_period_months", "total_interest", "total_repayable",
        "monthly_installment", "balance_remaining", "start_date", "status", "approved_by",
    ];

    protected $casts = ["start_date" => "date"];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    /**
     * Flat/simple interest over the full term — the standard, easiest to
     * explain to staff, and most common approach for a school-run staff
     * loan scheme (as opposed to a bank's reducing-balance method).
     * interest_rate is a flat rate for the whole loan term, not annualised —
     * e.g. "10%" on a 6-month KES 30,000 loan means KES 3,000 total
     * interest over the 6 months, not 10% per year.
     */
    public static function calculateTerms(float $principal, float $interestRatePercent, int $months): array
    {
        $totalInterest = round($principal * ($interestRatePercent / 100), 2);
        $totalRepayable = round($principal + $totalInterest, 2);
        $monthlyInstallment = $months > 0 ? round($totalRepayable / $months, 2) : $totalRepayable;

        return [
            "total_interest" => $totalInterest,
            "total_repayable" => $totalRepayable,
            "monthly_installment" => $monthlyInstallment,
        ];
    }

    /**
     * Splits a repayment amount into principal/interest proportionally to
     * the loan's overall interest-to-principal ratio — consistent with the
     * flat-interest method above (each installment carries the same mix).
     */
    public function splitRepayment(float $amount): array
    {
        if ($this->total_repayable <= 0) {
            return ["principal_portion" => $amount, "interest_portion" => 0];
        }

        $interestShare = $this->total_interest / $this->total_repayable;
        $interestPortion = round($amount * $interestShare, 2);
        $principalPortion = round($amount - $interestPortion, 2);

        return ["principal_portion" => $principalPortion, "interest_portion" => $interestPortion];
    }
}
