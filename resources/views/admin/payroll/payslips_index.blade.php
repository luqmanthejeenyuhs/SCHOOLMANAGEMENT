@extends('layouts.app')
@section('title', 'Payslips')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Payslips</h3>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-dark btn-sm">Back to Staff</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>Period</th><th>Gross Pay</th><th>Total Deductions</th><th>Net Pay</th><th></th></tr></thead>
            <tbody>
            @forelse($payslips as $payslip)
                <tr>
                    <td>{{ $payslip->employee->name }}</td>
                    <td>{{ $payslip->periodLabel() }}</td>
                    <td>KES {{ number_format($payslip->gross_pay, 2) }}</td>
                    <td>KES {{ number_format($payslip->total_deductions, 2) }}</td>
                    <td class="fw-bold">KES {{ number_format($payslip->net_pay, 2) }}</td>
                    <td class="text-end"><a href="{{ route('admin.payslips.show', $payslip) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No payslips generated yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payslips->links() }}</div>
@endsection
