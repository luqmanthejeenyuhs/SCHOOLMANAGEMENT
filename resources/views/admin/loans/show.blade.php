@extends('layouts.app')
@section('title', 'Loan Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ $loan->employee->name }} — {{ ucfirst($loan->loan_type) }} <span class="badge bg-secondary text-capitalize">{{ $loan->interest_method }} interest</span></h3>
    <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary btn-sm">Back to Loans</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Principal</div><div class="fs-5 fw-bold">KES {{ number_format($loan->principal, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Total Repayable</div><div class="fs-5 fw-bold">KES {{ number_format($loan->total_repayable, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Monthly Installment</div><div class="fs-5 fw-bold">KES {{ number_format($loan->monthly_installment, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Balance Remaining</div><div class="fs-5 fw-bold">KES {{ number_format($loan->balance_remaining, 2) }}</div></div></div>
</div>

@if($loan->status === 'active')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6>Record Manual Repayment</h6>
            <p class="text-muted small">For a repayment made outside payroll — e.g. the employee pays cash directly. Regular installments deduct automatically from their payslip each month.</p>
            <form method="POST" action="{{ route('admin.loans.repayments.store', $loan) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Amount (balance: KES {{ number_format($loan->balance_remaining, 2) }})</label>
                    <input type="text" inputmode="numeric" name="amount" class="form-control currency-input" placeholder="KSh 0.00" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Method</label>
                    <select name="method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mpesa">M-Pesa</option>
                    </select>
                </div>
                <button class="btn btn-dark">Save Repayment</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6>Write Off</h6>
            <p class="text-muted small">Marks the remaining balance as unrecoverable (e.g. the employee has left and won't repay). This does not reverse the original disbursement in your ledger.</p>
            <form method="POST" action="{{ route('admin.loans.write-off', $loan) }}" onsubmit="return confirm('Write off the remaining KES {{ number_format($loan->balance_remaining, 2) }} balance? This cannot be undone.');">
                @csrf
                <button class="btn btn-outline-danger">Write Off Remaining Balance</button>
            </form>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">Repayment History</div>
    <table class="table mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Amount</th><th>Principal</th><th>Interest</th><th>Source</th></tr></thead>
        <tbody>
        @forelse($loan->repayments as $r)
            <tr>
                <td>{{ $r->payment_date->format('d M Y') }}</td>
                <td>KES {{ number_format($r->amount, 2) }}</td>
                <td>KES {{ number_format($r->principal_portion, 2) }}</td>
                <td>KES {{ number_format($r->interest_portion, 2) }}</td>
                <td>{{ $r->payslip_id ? 'Payroll deduction' : 'Manual' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No repayments recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
