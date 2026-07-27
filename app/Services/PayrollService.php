<?php

namespace App\Services;

class PayrollService
{
    /**
     * Compute a full payslip breakdown for a given gross pay.
     * Returns an array ready to be persisted onto the payslips table.
     */
    public function calculate(float $basicSalary, float $allowancesTotal): array
    {
        $grossPay = $basicSalary + $allowancesTotal;

        $nssf = $this->calculateNssf($grossPay);
        $shif = $this->calculateShif($grossPay);
        $housingLevy = round($grossPay * config('payroll.housing_levy.rate'), 2);

        // Taxable pay = gross pay less NSSF and SHIF (both are pre-tax deductible per KRA rules)
        $taxablePay = max(0, $grossPay - $nssf - $shif);
        $grossPaye = $this->calculatePaye($taxablePay);
        $relief = config('payroll.paye.personal_relief');
        $paye = max(0, round($grossPaye - $relief, 2));

        $totalDeductions = round($paye + $shif + $nssf + $housingLevy, 2);
        $netPay = round($grossPay - $totalDeductions, 2);

        return [
            'basic_salary' => round($basicSalary, 2),
            'allowances_total' => round($allowancesTotal, 2),
            'gross_pay' => round($grossPay, 2),
            'paye' => $paye,
            'personal_relief' => $relief,
            'shif' => $shif,
            'nssf' => $nssf,
            'housing_levy' => $housingLevy,
            'other_deductions' => 0,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
        ];
    }

    /**
     * Fold an unpaid-leave/absence deduction into an already-computed breakdown
     * (statutory deductions are computed on contractual pay, not "pay actually
     * worked", so this is applied as an extra line item afterwards rather than
     * being mixed into the gross/taxable pay calculation above).
     */
    public function applyUnpaidLeaveDeduction(array $breakdown, int $unpaidDays): array
    {
        if ($unpaidDays <= 0) {
            return $breakdown;
        }

        $dailyRate = $breakdown['basic_salary'] / max(1, config('payroll.working_days_per_month'));
        $deduction = round($dailyRate * $unpaidDays, 2);

        $breakdown['other_deductions'] = round($breakdown['other_deductions'] + $deduction, 2);
        $breakdown['unpaid_leave_days'] = $unpaidDays;
        $breakdown['total_deductions'] = round($breakdown['total_deductions'] + $deduction, 2);
        $breakdown['net_pay'] = round($breakdown['net_pay'] - $deduction, 2);

        return $breakdown;
    }

    protected function calculatePaye(float $taxablePay): float
    {
        $bands = config('payroll.paye.bands');
        $tax = 0.0;
        $previousLimit = 0;

        foreach ($bands as [$upperLimit, $rate]) {
            if ($taxablePay <= $previousLimit) {
                break;
            }

            $bandAmount = min($taxablePay, $upperLimit) - $previousLimit;
            $tax += $bandAmount * $rate;
            $previousLimit = $upperLimit;
        }

        return round($tax, 2);
    }

    protected function calculateShif(float $grossPay): float
    {
        $computed = $grossPay * config('payroll.shif.rate');

        return round(max($computed, config('payroll.shif.minimum')), 2);
    }

    protected function calculateNssf(float $grossPay): float
    {
        $tier1Limit = config('payroll.nssf.tier1_limit');
        $tier2Limit = config('payroll.nssf.tier2_limit');
        $rate = config('payroll.nssf.rate');

        $tier1Pay = min($grossPay, $tier1Limit);
        $tier1 = $tier1Pay * $rate;

        $tier2Pay = max(0, min($grossPay, $tier2Limit) - $tier1Limit);
        $tier2 = $tier2Pay * $rate;

        return round($tier1 + $tier2, 2);
    }
}
