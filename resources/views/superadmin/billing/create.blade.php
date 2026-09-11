@extends('layouts.app')
@section('title', 'New Invoice')
@section('content')

<h3 class="mb-3">New Platform Invoice</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card p-4" style="max-width:600px;">
    <form method="POST" action="{{ route('superadmin.billing.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">School</label>
            <select name="school_id" class="form-select" required>
                <option value="">Select school</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}" @selected(old('school_id') == $s->id)>{{ $s->name }} ({{ ucfirst($s->plan) }})</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Amount (KES)</label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Billing Cycle</label>
                <select name="billing_cycle" class="form-select" required>
                    <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>Monthly</option>
                    <option value="termly" @selected(old('billing_cycle') === 'termly')>Termly</option>
                    <option value="annual" @selected(old('billing_cycle') === 'annual')>Annual</option>
                    <option value="one_off" @selected(old('billing_cycle') === 'one_off')>One-off</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Note (optional)</label>
            <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
        </div>
        <button class="btn btn-dark">Create Invoice</button>
        <a href="{{ route('superadmin.billing.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

@endsection
