<?php

/*
|--------------------------------------------------------------------------
| Kenya Statutory Payroll Rates
|--------------------------------------------------------------------------
| These figures reflect Kenya's payroll statutory framework as commonly
| published for the 2024/2025 tax year (Finance Act 2023, SHIF Act 2023,
| NSSF Act 2013 enhanced rates, Affordable Housing Levy). Rates, bands,
| reliefs, and NSSF earning limits are periodically revised by KRA/NSSF/SHIF
| — always confirm the current figures on kra.go.ke / nssf.or.ke / shif.go.ke
| before running real payroll, and update the numbers below accordingly.
*/

return [
    'paye' => [
        // Monthly graduated tax bands: [upper_limit, rate]. Use PHP_INT_MAX for the top band.
        'bands' => [
            [24000, 0.10],
            [32333, 0.25],
            [500000, 0.30],
            [800000, 0.325],
            [PHP_INT_MAX, 0.35],
        ],
        'personal_relief' => 2400, // KES per month
    ],

    'shif' => [
        'rate' => 0.0275, // 2.75% of gross pay
        'minimum' => 300, // KES minimum monthly contribution
    ],

    'nssf' => [
        'tier1_limit' => 8000,   // Lower Earnings Limit
        'tier2_limit' => 72000,  // Upper Earnings Limit
        'rate' => 0.06,          // 6% employee contribution on pensionable pay
    ],

    'housing_levy' => [
        'rate' => 0.015, // 1.5% of gross pay (Affordable Housing Levy, employee portion)
    ],

    // Used to convert unpaid-leave/absence days into a per-day salary deduction:
    // daily_rate = basic_salary / working_days_per_month
    'working_days_per_month' => env('PAYROLL_WORKING_DAYS_PER_MONTH', 26),
];
