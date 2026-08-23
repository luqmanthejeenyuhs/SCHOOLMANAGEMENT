@extends('layouts.app')
@section('title', 'Leave Requests')
@section('content')
<h3 class="mb-3">Leave Requests</h3>

@if(!$employee)
    <div class="card p-4"><p class="text-muted mb-0">No staff record is linked to your account yet. Ask an admin to link it before you can request leave.</p></div>
@else
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="mb-3">Request Leave</h6>
            <form method="POST" action="{{ route('staff.leave.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small text-muted">Type</label>
                    <select name="leave_type" class="form-select" required>
                        @foreach(\App\Models\LeaveRequest::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small text-muted">From</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">To</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label small text-muted">Reason</label>
                    <textarea name="reason" class="form-control" rows="3"></textarea>
                </div>
                <button class="btn btn-dark w-100 mt-3">Submit Request</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td>{{ \App\Models\LeaveRequest::TYPES[$r->leave_type] ?? $r->leave_type }}</td>
                        <td class="small">{{ $r->start_date->format('d M') }} – {{ $r->end_date->format('d M Y') }}</td>
                        <td>{{ $r->days }}</td>
                        <td>
                            <span class="badge bg-{{ match($r->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning text-dark' } }}">{{ ucfirst($r->status) }}</span>
                            @if($r->status === 'rejected' && $r->review_note)
                                <div class="text-muted small mt-1">{{ $r->review_note }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
