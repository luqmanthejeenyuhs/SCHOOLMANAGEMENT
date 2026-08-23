@extends('layouts.app')
@section('title', 'Loans & Advances')
@section('content')
<h3 class="mb-3">Loans &amp; Advances</h3>

<div class="card p-3 mb-3">
    <div class="btn-group btn-group-sm">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'disbursed' => 'Disbursed', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.loan_requests.index', ['status' => $key]) }}"
               class="btn {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>Staff</th>
                <th>Type</th>
                <th>Amount (KES)</th>
                <th>Reason</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td>{{ $r->employee->name ?? 'N/A' }}</td>
                <td>{{ \App\Models\LoanRequest::TYPES[$r->request_type] ?? $r->request_type }}</td>
                <td class="font-monospace">{{ number_format($r->amount, 2) }}</td>
                <td class="text-muted small">{{ $r->reason ?? '-' }}</td>
                <td>
                    <span class="badge bg-{{ match($r->status) { 'approved' => 'info', 'disbursed' => 'success', 'rejected' => 'danger', default => 'warning text-dark' } }}">
                        {{ ucfirst($r->status) }}
                    </span>
                    @if($r->reviewedBy)
                        <div class="text-muted small mt-1">by {{ $r->reviewedBy->name }}</div>
                    @endif
                </td>
                <td class="text-end">
                    @if($r->status === 'pending')
                        <form method="POST" action="{{ route('admin.loan_requests.approve', $r) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Approve</button>
                        </form>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reject-{{ $r->id }}">Reject</button>
                    @elseif($r->status === 'approved')
                        <form method="POST" action="{{ route('admin.loan_requests.disburse', $r) }}" class="d-inline" onsubmit="return confirm('Confirm the money has actually been paid out?')">
                            @csrf
                            <button class="btn btn-sm btn-dark">Mark Disbursed</button>
                        </form>
                    @endif
                </td>
            </tr>

            @if($r->status === 'pending')
            <div class="modal fade" id="reject-{{ $r->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.loan_requests.reject', $r) }}">
                            @csrf
                            <div class="modal-header">
                                <h6 class="modal-title">Reject request — {{ $r->employee->name ?? 'N/A' }}</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label small text-muted">Reason (optional, visible to the staff member)</label>
                                <input type="text" name="review_note" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">No {{ $status !== 'all' ? $status . ' ' : '' }}requests.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>

<div class="alert alert-secondary mt-3 small">
    Note: approving a loan/advance here doesn't automatically deduct it from payroll yet — when you run this person's next payslip in Payroll &amp; Payslips, add the repayment as a manual deduction. Automatic repayment scheduling can be added later if you need it.
</div>
@endsection
