@extends('layouts.app')
@section('title', 'Statutory Deductions')
@section('content')
@include('admin.payroll._tabs')

<h5 class="mb-2">Kenya Statutory Deductions</h5>
<p class="text-muted small">These are calculated automatically on every payslip from gross pay — nothing to configure. Shown here for reference only.</p>
<div class="card mb-4">
    <table class="table mb-0">
        <tbody>
        @foreach($statutory as $s)
            <tr><td>{{ $s->name }}</td><td class="text-end text-muted">Calculated automatically</td></tr>
        @endforeach
        </tbody>
    </table>
</div>

<h5 class="mb-2">Custom Deductions (SACCO, Union Dues, etc.)</h5>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Add Deduction Type</h6>
            <form method="POST" action="{{ route('admin.deduction-types.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. SACCO Savings" required>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_percentage" value="1" id="isPct">
                    <label class="form-check-label small" for="isPct">Percentage of gross pay (instead of fixed amount)</label>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Default Rate / Amount</label>
                    <input type="number" step="0.01" min="0" name="rate_or_amount" class="form-control" required>
                </div>
                <button class="btn btn-dark w-100">Add</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Rate/Amount</th><th>Employees Assigned</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($custom as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->is_percentage ? $c->rate_or_amount.'%' : 'KES '.number_format($c->rate_or_amount, 2) }}</td>
                        <td>{{ $c->employee_deductions_count }}</td>
                        <td>@if($c->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No custom deduction types yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($custom->isNotEmpty())
<h5 class="mb-2">Assign a Deduction to an Employee</h5>
<div class="card p-3" style="max-width:600px;">
    <form method="POST" action="{{ route('admin.deduction-types.assign') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
            <label class="form-label small">Employee</label>
            <select name="employee_id" class="form-select" required>
                @foreach(\App\Models\Employee::orderBy('name')->get() as $e)
                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Deduction Type</label>
            <select name="deduction_type_id" class="form-select" required>
                @foreach($custom as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Amount</label>
            <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Assign</button>
        </div>
    </form>
</div>
@endif
@endsection
