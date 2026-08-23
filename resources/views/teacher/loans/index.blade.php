@extends('layouts.app')
@section('title', 'Loans & Advances')
@section('content')
<h3 class="mb-3">Loans &amp; Advances</h3>

@if(!$employee)
    <div class="card p-4"><p class="text-muted mb-0">No staff record is linked to your account yet. Ask an admin to link it before you can request a loan or advance.</p></div>
@else
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="mb-3">Request a Loan or Advance</h6>
            <form method="POST" action="{{ route('staff.loans.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small text-muted">Type</label>
                    <select name="request_type" class="form-select" required>
                        @foreach(\App\Models\LoanRequest::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Amount (KES)</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Reason</label>
                    <textarea name="reason" class="form-control" rows="3"></textarea>
                </div>
                <button class="btn btn-dark w-100 mt-2">Submit Request</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Type</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td>{{ \App\Models\LoanRequest::TYPES[$r->request_type] ?? $r->request_type }}</td>
                        <td class="font-monospace">{{ number_format($r->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ match($r->status) { 'approved' => 'info', 'disbursed' => 'success', 'rejected' => 'danger', default => 'warning text-dark' } }}">{{ ucfirst($r->status) }}</span>
                            @if($r->status === 'rejected' && $r->review_note)
                                <div class="text-muted small mt-1">{{ $r->review_note }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
