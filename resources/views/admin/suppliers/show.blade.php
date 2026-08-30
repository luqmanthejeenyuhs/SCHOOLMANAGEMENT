@extends('layouts.app')
@section('title', $supplier->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ $supplier->name }}</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.suppliers.bills.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Bill</a>
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline-dark">Edit</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Owed</div>
            <div class="fs-4 fw-bold">KES {{ number_format($supplier->totalOwed(), 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">Payment Terms</div>
            <div class="fs-4 fw-bold">{{ $supplier->payment_terms_days }} days</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">KRA PIN</div>
            <div class="fs-4 fw-bold">{{ $supplier->kra_pin ?? '—' }}</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Bills</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Date</th><th>Description</th><th>Total</th><th>Balance</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($supplier->bills as $bill)
                <tr>
                    <td>{{ $bill->bill_date->format('d M Y') }}</td>
                    <td>{{ $bill->description }}</td>
                    <td>KES {{ number_format($bill->total_amount, 2) }}</td>
                    <td>KES {{ number_format($bill->balance(), 2) }}</td>
                    <td class="{{ $bill->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $bill->due_date->format('d M Y') }}</td>
                    <td>
                        @if($bill->status === 'paid')<span class="badge bg-success">Paid</span>
                        @elseif($bill->status === 'partially_paid')<span class="badge bg-warning text-dark">Partial</span>
                        @else<span class="badge bg-danger">Unpaid</span>@endif
                    </td>
                    <td><a href="{{ route('admin.suppliers.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No bills yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">Credit Notes</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Date</th><th>Credit Note #</th><th>Reason</th><th>Amount</th></tr></thead>
            <tbody>
            @forelse($supplier->creditNotes as $cn)
                <tr>
                    <td>{{ $cn->date->format('d M Y') }}</td>
                    <td>{{ $cn->creditNoteNumber() }}</td>
                    <td>{{ $cn->reason }}</td>
                    <td>KES {{ number_format($cn->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No credit notes issued.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
