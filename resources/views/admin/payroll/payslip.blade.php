@extends('layouts.app')
@section('title', 'Payslip')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3>Payslip</h3>
    <button class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card p-4" style="max-width:700px;">
    <div class="text-center mb-3">
        <h5 class="mb-0">PAYSLIP</h5>
        <small class="text-muted">{{ $payslip->periodLabel() }}</small>
    </div>

    <div class="row mb-3">
        <div class="col-6"><strong>Employee:</strong> {{ $payslip->employee->name }}</div>
        <div class="col-6"><strong>Job Title:</strong> {{ $payslip->employee->job_title }}</div>
        <div class="col-6 mt-1"><strong>KRA PIN:</strong> {{ $payslip->employee->kra_pin ?? '—' }}</div>
        <div class="col-6 mt-1"><strong>NSSF No:</strong> {{ $payslip->employee->nssf_number ?? '—' }}</div>
        <div class="col-6 mt-1"><strong>SHIF No:</strong> {{ $payslip->employee->shif_number ?? '—' }}</div>
    </div>

    <table class="table table-sm">
        <thead class="table-light"><tr><th>Earnings</th><th class="text-end">Amount (KES)</th></tr></thead>
        <tbody>
            <tr><td>Basic Salary</td><td class="text-end">{{ number_format($payslip->basic_salary, 2) }}</td></tr>
            <tr><td>Allowances (House + Transport + Other)</td><td class="text-end">{{ number_format($payslip->allowances_total, 2) }}</td></tr>
            <tr class="fw-bold"><td>Gross Pay</td><td class="text-end">{{ number_format($payslip->gross_pay, 2) }}</td></tr>
        </tbody>
    </table>

    <table class="table table-sm">
        <thead class="table-light"><tr><th>Statutory Deductions</th><th class="text-end">Amount (KES)</th></tr></thead>
        <tbody>
            <tr><td>NSSF (Tier I &amp; II)</td><td class="text-end">{{ number_format($payslip->nssf, 2) }}</td></tr>
            <tr><td>SHIF (2.75% of gross)</td><td class="text-end">{{ number_format($payslip->shif, 2) }}</td></tr>
            <tr><td>Affordable Housing Levy (1.5% of gross)</td><td class="text-end">{{ number_format($payslip->housing_levy, 2) }}</td></tr>
            <tr><td>PAYE (after personal relief of KES {{ number_format($payslip->personal_relief, 2) }})</td><td class="text-end">{{ number_format($payslip->paye, 2) }}</td></tr>
            @foreach($payslip->items as $item)
            <tr><td>{{ $item->label }}</td><td class="text-end">{{ number_format($item->amount, 2) }}</td></tr>
            @endforeach
            <tr class="fw-bold"><td>Total Deductions</td><td class="text-end">{{ number_format($payslip->total_deductions, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
        <strong>NET PAY</strong>
        <strong class="fs-4 text-success">KES {{ number_format($payslip->net_pay, 2) }}</strong>
    </div>

    <p class="small text-muted mt-3 mb-0">
        Statutory rates (PAYE bands, SHIF, NSSF Tier I/II limits, Housing Levy) follow Kenya's current
        framework, including Housing Levy as a pre-tax deduction, and are configurable in
        <code>config/payroll.php</code>. NSSF Tier II's upper earnings limit is disputed across current
        sources (KES 72,000 vs 108,000) — verify directly at nssf.or.ke before relying on this for a real
        payroll run.
    </p>
</div>

<style>
@media print {
    .navbar, .sidebar, .no-print { display: none !important; }
}
</style>
@endsection
