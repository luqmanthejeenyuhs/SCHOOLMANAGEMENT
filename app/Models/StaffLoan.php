<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLoan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "employee_id", "loan_type", "interest_method", "principal", "interest_rate",
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
     * Three interest methods, matching how the school actually explains
     * these to staff:
     *
     * - "flat": interest_rate is a single rate for the WHOLE loan term,
     *   charged once, added to principal immediately. E.g. 10% flat on a
     *   6-month KES 30,000 loan = KES 3,000 interest total, full stop.
     *
     * - "simple": interest_rate is an ANNUAL rate, pro-rated by the actual
     *   term length (months/12) — the standard "simple interest" formula
     *   (I = P × R × T). A 6-month loan at 12% simple interest carries half
     *   a year's worth of interest, not the full 12%.
     *
     * - "compound": interest_rate is a MONTHLY rate, and the loan is
     *   amortised like a real bank loan — a fixed monthly installment where
     *   the interest portion is calculated on the outstanding balance each
     *   month (reducing balance), so unpaid interest doesn't just vanish
     *   the way flat/simple's proportional split would ignore it. This is
     *   what makes a 12%-per-month compound loan grow so much faster.
     */
    public static function calculateTerms(float $principal, float $interestRatePercent, int $months, string $interestMethod = "flat"): array
    {
        return match ($interestMethod) {
            "simple" => self::simpleInterestTerms($principal, $interestRatePercent, $months),
            "compound" => self::compoundInterestTerms($principal, $interestRatePercent, $months),
            default => self::flatInterestTerms($principal, $interestRatePercent, $months),
        };
    }

    protected static function flatInterestTerms(float $principal, float $ratePercent, int $months): array
    {
        $totalInterest = round($principal * ($ratePercent / 100), 2);
        $totalRepayable = round($principal + $totalInterest, 2);
        $monthlyInstallment = $months > 0 ? round($totalRepayable / $months, 2) : $totalRepayable;

        return [
            "total_interest" => $totalInterest,
            "total_repayable" => $totalRepayable,
            "monthly_installment" => $monthlyInstallment,
        ];
    }

    protected static function simpleInterestTerms(float $principal, float $annualRatePercent, int $months): array
    {
        $years = $months / 12;
        $totalInterest = round($principal * ($annualRatePercent / 100) * $years, 2);
        $totalRepayable = round($principal + $totalInterest, 2);
        $monthlyInstallment = $months > 0 ? round($totalRepayable / $months, 2) : $totalRepayable;

        return [
            "total_interest" => $totalInterest,
            "total_repayable" => $totalRepayable,
            "monthly_installment" => $monthlyInstallment,
        ];
    }

    protected static function compoundInterestTerms(float $principal, float $monthlyRatePercent, int $months): array
    {
        $r = $monthlyRatePercent / 100;

        if ($r <= 0 || $months <= 0) {
            $monthlyInstallment = $months > 0 ? round($principal / $months, 2) : $principal;
            $totalRepayable = round($monthlyInstallment * max($months, 1), 2);

            return [
                "total_interest" => round($totalRepayable - $principal, 2),
                "total_repayable" => $totalRepayable,
                "monthly_installment" => $monthlyInstallment,
            ];
        }

        // Standard amortisation (reducing balance) formula — the same one
        // banks and SACCOs use for a fixed monthly payment on a compounding
        // loan: installment = P × r(1+r)^n / ((1+r)^n − 1)
        $factor = pow(1 + $r, $months);
        $monthlyInstallment = round($principal * $r * $factor / ($factor - 1), 2);
        $totalRepayable = round($monthlyInstallment * $months, 2);
        $totalInterest = round($totalRepayable - $principal, 2);

        return [
            "total_interest" => $totalInterest,
            "total_repayable" => $totalRepayable,
            "monthly_installment" => $monthlyInstallment,
        ];
    }

    /**
     * Splits a repayment into principal/interest. Flat and simple interest
     * use a proportional split (each installment carries the same
     * interest-to-principal mix, matching how the total was calculated).
     * Compound interest instead calculates interest on whatever balance is
     * still outstanding right now — the correct reducing-balance method —
     * which is why $balanceBeforePayment matters for this one.
     */
    public function splitRepayment(float $amount, ?float $balanceBeforePayment = null): array
    {
        if ($this->interest_method === "compound") {
            $balanceBeforePayment ??= $this->balance_remaining;
            $monthlyRate = $this->interest_rate / 100;
            $interestPortion = round($balanceBeforePayment * $monthlyRate, 2);
            $interestPortion = min($interestPortion, $amount);
            $principalPortion = round($amount - $interestPortion, 2);

            return ["principal_portion" => $principalPortion, "interest_portion" => $interestPortion];
        }

        if ($this->total_repayable <= 0) {
            return ["principal_portion" => $amount, "interest_portion" => 0];
        }

        $interestShare = $this->total_interest / $this->total_repayable;
        $interestPortion = round($amount * $interestShare, 2);
        $principalPortion = round($amount - $interestPortion, 2);

        return ["principal_portion" => $principalPortion, "interest_portion" => $interestPortion];
    }
}
