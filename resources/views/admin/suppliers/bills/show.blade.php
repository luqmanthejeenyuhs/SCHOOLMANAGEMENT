@extends('layouts.app')
@section('title', 'Bill Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Bill — {{ $bill->supplier->name }}</h3>
    <a href="{{ route('admin.suppliers.bills.index') }}" class="btn btn-outline-secondary btn-sm">Back to Bills</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Total</div><div class="fs-5 fw-bold">KES {{ number_format($bill->total_amount, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">VAT</div><div class="fs-5 fw-bold">KES {{ number_format($bill->vat_amount, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Balance Due</div><div class="fs-5 fw-bold">KES {{ number_format($bill->balance(), 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Due Date</div><div class="fs-5 fw-bold {{ $bill->isOverdue() ? 'text-danger' : '' }}">{{ $bill->due_date->format('d M Y') }}</div></div></div>
</div>

<div class="card p-3 mb-4">
    <strong>{{ $bill->description }}</strong>
    @if($bill->bill_reference)<div class="text-muted small">Ref: {{ $bill->bill_reference }}</div>@endif
</div>

@if($bill->balance() > 0)
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6>Record Payment</h6>
            <form method="POST" action="{{ route('admin.suppliers.bills.payments.store', $bill) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Amount (balance: KES {{ number_format($bill->balance(), 2) }})</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $bill->balance() }}" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Method</label>
                    <select name="method" class="form-select">
                        <option value="bank">Bank Transfer</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Reference</label>
                    <input type="text" name="reference" class="form-control" placeholder="Transaction/cheque no.">
                </div>
                <button class="btn btn-dark">Save Payment</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6>Issue Credit Note</h6>
            <p class="text-muted small">Use this for returned goods, an overcharge, or any reduction to what's owed — not for recording a payment.</p>
            <form method="POST" action="{{ route('admin.suppliers.bills.credit-notes.store', $bill) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Amount (max: KES {{ number_format($bill->balance(), 2) }})</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $bill->balance() }}" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Reason</label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g. Returned damaged items" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <button class="btn btn-outline-dark">Issue Credit Note</button>
            </form>
        </div>
    </div>
</div>
@endif

<div class="card mb-3">
    <div class="card-header">Payments</div>
    <table class="table mb-0"><thead class="table-light"><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
        <tbody>
        @forelse($bill->payments as $p)
            <tr><td>{{ $p->payment_date->format('d M Y') }}</td><td>KES {{ number_format($p->amount, 2) }}</td><td class="text-capitalize">{{ $p->method }}</td><td>{{ $p->reference ?? '—' }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No payments yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <div class="card-header">Credit Notes</div>
    <table class="table mb-0"><thead class="table-light"><tr><th>Date</th><th>Credit Note #</th><th>Reason</th><th>Amount</th></tr></thead>
        <tbody>
        @forelse($bill->creditNotes as $cn)
            <tr><td>{{ $cn->date->format('d M Y') }}</td><td>{{ $cn->creditNoteNumber() }}</td><td>{{ $cn->reason }}</td><td>KES {{ number_format($cn->amount, 2) }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">None issued.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
