@extends('layouts.app')
@section('title', 'My Payslips')
@section('content')
<h3 class="mb-3">My Payslips</h3>

@if(!$employee)
    <div class="card p-4"><p class="text-muted mb-0">No staff record is linked to your account yet. Ask an admin to link it.</p></div>
@else
<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>Period</th>
                <th class="text-end">Gross Pay</th>
                <th class="text-end">Deductions</th>
                <th class="text-end">Net Pay</th>
            </tr>
        </thead>
        <tbody>
        @forelse($payslips as $p)
            <tr style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#details-{{ $p->id }}">
                <td>{{ $p->periodLabel() }}</td>
                <td class="text-end font-monospace">{{ number_format($p->gross_pay, 2) }}</td>
                <td class="text-end font-monospace">{{ number_format($p->total_deductions, 2) }}</td>
                <td class="text-end font-monospace fw-semibold">{{ number_format($p->net_pay, 2) }}</td>
            </tr>
            <tr class="collapse" id="details-{{ $p->id }}">
                <td colspan="4" class="bg-light">
                    <div class="row small py-2">
                        <div class="col-md-3">Basic Salary: <span class="font-monospace">{{ number_format($p->basic_salary, 2) }}</span></div>
                        <div class="col-md-3">Allowances: <span class="font-monospace">{{ number_format($p->allowances_total, 2) }}</span></div>
                        <div class="col-md-3">PAYE: <span class="font-monospace">{{ number_format($p->paye, 2) }}</span></div>
                        <div class="col-md-3">Personal Relief: <span class="font-monospace">{{ number_format($p->personal_relief, 2) }}</span></div>
                        <div class="col-md-3">SHIF: <span class="font-monospace">{{ number_format($p->shif, 2) }}</span></div>
                        <div class="col-md-3">NSSF: <span class="font-monospace">{{ number_format($p->nssf, 2) }}</span></div>
                        <div class="col-md-3">Housing Levy: <span class="font-monospace">{{ number_format($p->housing_levy, 2) }}</span></div>
                        <div class="col-md-3">Other Deductions: <span class="font-monospace">{{ number_format($p->other_deductions, 2) }}</span></div>
                        @if($p->unpaid_leave_days > 0)
                        <div class="col-md-3">Unpaid Leave Days: <span class="font-monospace">{{ $p->unpaid_leave_days }}</span></div>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No payslips generated yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection
