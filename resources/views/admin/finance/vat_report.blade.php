@extends('layouts.app')
@section('title', 'VAT Report')
@section('content')
<h3 class="mb-3">VAT Report</h3>
<p class="text-muted">Input VAT paid to suppliers, reclaimable against your KRA VAT return. School fees are VAT-exempt educational services, so there is no output VAT to reconcile here.</p>

<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Filter</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Net Amount</div><div class="fs-5 fw-bold">KES {{ number_format($totalNet, 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Input VAT (Reclaimable)</div><div class="fs-5 fw-bold">KES {{ number_format($totalVat, 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="text-muted small">Gross Total</div><div class="fs-5 fw-bold">KES {{ number_format($totalGross, 2) }}</div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Date</th><th>Supplier</th><th>Description</th><th>Net</th><th>VAT</th><th>Total</th></tr></thead>
            <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->bill_date->format('d M Y') }}</td>
                    <td>{{ $bill->supplier->name }}</td>
                    <td>{{ $bill->description }}</td>
                    <td>KES {{ number_format($bill->amount, 2) }}</td>
                    <td>KES {{ number_format($bill->vat_amount, 2) }}</td>
                    <td>KES {{ number_format($bill->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No VAT-bearing bills in this period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
