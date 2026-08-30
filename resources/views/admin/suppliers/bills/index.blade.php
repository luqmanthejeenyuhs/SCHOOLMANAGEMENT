@extends('layouts.app')
@section('title', 'Supplier Bills')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Supplier Bills</h3>
    <a href="{{ route('admin.suppliers.bills.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Bill</a>
</div>

<div class="card p-3 mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small">Supplier</label>
            <select name="supplier_id" class="form-select" onchange="this.form.submit()">
                <option value="">All suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Date</th><th>Supplier</th><th>Description</th><th>Total (incl. VAT)</th><th>Balance</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->bill_date->format('d M Y') }}</td>
                    <td>{{ $bill->supplier->name }}</td>
                    <td>{{ $bill->description }}</td>
                    <td>KES {{ number_format($bill->total_amount, 2) }}</td>
                    <td>KES {{ number_format($bill->balance(), 2) }}</td>
                    <td class="{{ $bill->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $bill->due_date->format('d M Y') }} @if($bill->isOverdue())<span class="badge bg-danger">Overdue</span>@endif</td>
                    <td>
                        @if($bill->status === 'paid')<span class="badge bg-success">Paid</span>
                        @elseif($bill->status === 'partially_paid')<span class="badge bg-warning text-dark">Partial</span>
                        @else<span class="badge bg-danger">Unpaid</span>@endif
                    </td>
                    <td><a href="{{ route('admin.suppliers.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No bills recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $bills->links() }}</div>
@endsection
