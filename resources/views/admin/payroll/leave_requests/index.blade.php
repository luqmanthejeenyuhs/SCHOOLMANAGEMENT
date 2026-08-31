@extends('layouts.app')
@section('title', 'Leave Requests')
@section('content')
@include('admin.payroll._tabs')

<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Record Leave Request</h6>
            <form method="POST" action="{{ route('admin.leave-requests.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Employee</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Type</label>
                    <select name="leave_type" class="form-select">
                        <option value="annual">Annual</option>
                        <option value="sick">Sick</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="maternity">Maternity</option>
                        <option value="paternity">Paternity</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Reason (optional)</label>
                    <textarea name="reason" class="form-control" rows="2"></textarea>
                </div>
                <button class="btn btn-dark w-100">Save</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 mb-3">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td>{{ $r->employee->name }}</td>
                        <td class="text-capitalize">{{ $r->leave_type }}</td>
                        <td>{{ $r->start_date->format('d M') }} — {{ $r->end_date->format('d M Y') }}</td>
                        <td>{{ $r->days() }}</td>
                        <td>
                            @if($r->status === 'approved')<span class="badge bg-success">Approved</span>
                            @elseif($r->status === 'rejected')<span class="badge bg-danger">Rejected</span>
                            @else<span class="badge bg-warning text-dark">Pending</span>@endif
                        </td>
                        <td class="text-end">
                            @if($r->status === 'pending')
                            <form method="POST" action="{{ route('admin.leave-requests.decide', $r) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button class="btn btn-sm btn-outline-success">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.leave-requests.decide', $r) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No leave requests recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
