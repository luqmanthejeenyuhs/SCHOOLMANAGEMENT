@extends('layouts.app')
@section('title', 'Platform Billing')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-receipt"></i> Platform Billing</h3>
    <a href="{{ route('superadmin.billing.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Invoice</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Outstanding</span>
            <h4 class="mb-0 text-danger">KES {{ number_format($summary['outstanding'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Overdue</span>
            <h4 class="mb-0 text-warning">KES {{ number_format($summary['overdue'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Collected (all time)</span>
            <h4 class="mb-0 text-success">KES {{ number_format($summary['collected'], 2) }}</h4>
        </div>
    </div>
</div>

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
    <select name="school_id" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All schools</option>
        @foreach($schools as $s)
            <option value="{{ $s->id }}" @selected($schoolId == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(['pending', 'overdue', 'paid'] as $s)
            <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
</form>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>School</th><th>Amount</th><th>Cycle</th><th>Due</th><th>Status</th><th>Note</th><th style="width:180px;"></th></tr>
        </thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr>
                <td>{{ $inv->school->name ?? '—' }}</td>
                <td>KES {{ number_format($inv->amount, 2) }}</td>
                <td class="text-capitalize">{{ str_replace('_', ' ', $inv->billing_cycle) }}</td>
                <td>{{ $inv->due_date->format('d M Y') }}</td>
                <td>
                    @if($inv->status === 'paid') <span class="badge bg-success">Paid</span>
                    @elseif($inv->status === 'overdue') <span class="badge bg-danger">Overdue</span>
                    @else <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </td>
                <td class="small text-muted">{{ $inv->note }}</td>
                <td>
                    @if($inv->status !== 'paid')
                        <form method="POST" action="{{ route('superadmin.billing.mark-paid', $inv) }}" class="d-flex gap-1">
                            @csrf
                            <select name="paid_method" class="form-select form-select-sm" required>
                                <option value="">Method…</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            <button class="btn btn-sm btn-success">Paid</button>
                        </form>
                    @else
                        <span class="small text-muted">{{ $inv->paid_at?->format('d M Y') }} via {{ str_replace('_',' ',$inv->paid_method) }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No invoices yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $invoices->links() }}</div>

@endsection
