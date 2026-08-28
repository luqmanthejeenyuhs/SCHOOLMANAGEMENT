@extends('layouts.app')
@section('title', 'My Fees')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-cash-coin"></i> My Fees</h3>
    <small class="text-muted">{{ $student->schoolClass->name ?? '—' }} · Admission No: {{ $student->admission_no }}</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Total Billed</span>
            <h4 class="mb-0">KES {{ number_format($totalBilled, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Total Paid</span>
            <h4 class="mb-0 text-success">KES {{ number_format($totalPaid, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Balance</span>
            <h4 class="mb-0 {{ $feeBalance > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($feeBalance, 2) }}</h4>
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <h6>Invoices</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Fee Type</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th>Due</th></tr></thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr>
                <td>{{ $inv->feeType->name ?? '—' }}</td>
                <td>KES {{ number_format($inv->amount, 2) }}</td>
                <td>KES {{ number_format($inv->totalPaid(), 2) }}</td>
                <td>KES {{ number_format($inv->balance(), 2) }}</td>
                <td>
                    @if($inv->status === 'paid') <span class="badge bg-success">Paid</span>
                    @elseif($inv->status === 'partially_paid') <span class="badge bg-warning text-dark">Partial</span>
                    @else <span class="badge bg-danger">Unpaid</span>
                    @endif
                </td>
                <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">No invoices yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card p-3">
    <h6>Payment History</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Date</th><th>Fee Type</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
        <tbody>
        @forelse($payments as $p)
            <tr>
                <td>{{ $p->payment_date?->format('d M Y') }}</td>
                <td>{{ $p->invoice->feeType->name ?? '—' }}</td>
                <td>KES {{ number_format($p->amount_paid, 2) }}</td>
                <td class="text-capitalize">{{ str_replace('_', ' ', $p->method) }}</td>
                <td>{{ $p->reference ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
