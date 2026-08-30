@extends('layouts.app')
@section('title', 'Staff Loans & Advances')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Staff Loans &amp; Advances</h3>
    <a href="{{ route('admin.loans.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Loan / Advance</a>
</div>

<div class="card p-3 mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="written_off" @selected(request('status') === 'written_off')>Written Off</option>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>Type</th><th>Principal</th><th>Interest</th><th>Monthly Installment</th><th>Balance</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($loans as $loan)
                <tr>
                    <td>{{ $loan->employee->name }}</td>
                    <td class="text-capitalize">{{ $loan->loan_type }}</td>
                    <td>KES {{ number_format($loan->principal, 2) }}</td>
                    <td>{{ $loan->interest_rate }}%</td>
                    <td>KES {{ number_format($loan->monthly_installment, 2) }}</td>
                    <td>KES {{ number_format($loan->balance_remaining, 2) }}</td>
                    <td>
                        @if($loan->status === 'active')<span class="badge bg-primary">Active</span>
                        @elseif($loan->status === 'completed')<span class="badge bg-success">Completed</span>
                        @else<span class="badge bg-secondary">Written Off</span>@endif
                    </td>
                    <td><a href="{{ route('admin.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No loans or advances recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $loans->links() }}</div>
@endsection
