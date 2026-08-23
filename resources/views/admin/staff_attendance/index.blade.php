@extends('layouts.app')
@section('title', 'Staff Attendance')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Staff Attendance</h3>
    @if(auth()->user()->hasPermission('manage_staff_attendance'))
    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#mark-attendance">+ Mark Attendance</button>
    @endif
</div>

<div class="card p-3 mb-3">
    <form method="GET" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label mb-1">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
        </div>
        <button class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>Staff Name</th>
                <th>Job Title</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Method</th>
                <th>Status</th>
                <th>Marked By</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                <td>{{ $record->employee->name ?? 'N/A' }}</td>
                <td>{{ $record->employee->job_title ?? '-' }}</td>
                <td>{{ $record->clock_in ? $record->clock_in->format('g:i A') : '-' }}</td>
                <td>{{ $record->clock_out ? $record->clock_out->format('g:i A') : '-' }}</td>
                <td>
                    <span class="badge bg-{{ $record->method === 'geofence' ? 'info' : 'light text-dark border' }}">
                        {{ $record->method === 'geofence' ? 'On-compound' : 'Manual' }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-{{ match($record->status) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'on_leave' => 'secondary',
                        'half_day' => 'info',
                        default => 'light text-dark',
                    } }}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                </td>
                <td class="text-muted small">{{ $record->markedBy->name ?? 'Self' }}</td>
                <td class="text-end">
                    @if(auth()->user()->hasPermission('manage_staff_attendance'))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit-{{ $record->id }}">Edit</button>
                    @endif
                </td>
            </tr>

            @if(auth()->user()->hasPermission('manage_staff_attendance'))
            <div class="modal fade" id="edit-{{ $record->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.staff_attendance.update', $record) }}">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h6 class="modal-title">Edit — {{ $record->employee->name ?? 'N/A' }} ({{ $record->date->format('d M Y') }})</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted">Clock In</label>
                                        <input type="time" name="clock_in" class="form-control" value="{{ $record->clock_in?->format('H:i') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted">Clock Out</label>
                                        <input type="time" name="clock_out" class="form-control" value="{{ $record->clock_out?->format('H:i') }}">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label small text-muted">Status</label>
                                    <select name="status" class="form-select">
                                        @foreach(['present','late','absent','on_leave','half_day'] as $s)
                                        <option value="{{ $s }}" {{ $record->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label small text-muted">Remarks</label>
                                    <input type="text" name="remarks" class="form-control" value="{{ $record->remarks }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-dark btn-sm">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <tr><td colspan="8" class="text-center text-muted py-3">No attendance records for this date.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($unrecorded->count() > 0)
<div class="card mt-3 p-3">
    <h6 class="mb-2">Not yet recorded today ({{ $unrecorded->count() }})</h6>
    <div class="d-flex flex-wrap gap-2">
        @foreach($unrecorded as $emp)
            <span class="badge bg-light text-dark border">{{ $emp->name }}</span>
        @endforeach
    </div>
    <p class="text-muted small mt-2 mb-0">These staff haven't clocked in and nobody has marked them yet — use "+ Mark Attendance" above to record them (e.g. on_leave, absent, or a late manual entry).</p>
</div>
@endif

@if(auth()->user()->hasPermission('manage_staff_attendance'))
<div class="modal fade" id="mark-attendance" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.staff_attendance.store') }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">Mark Attendance</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Staff Member</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">— Select —</option>
                            @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->job_title }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted">Clock In</label>
                            <input type="time" name="clock_in" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Clock Out</label>
                            <input type="time" name="clock_out" class="form-control">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="on_leave">On Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small text-muted">Remarks (optional)</label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. Sick leave, verbal approval from HOD">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
