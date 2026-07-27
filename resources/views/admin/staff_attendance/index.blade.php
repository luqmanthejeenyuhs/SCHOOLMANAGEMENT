@extends('layouts.app')
@section('title', 'Staff Attendance')
@section('content')
<h3 class="mb-3">Staff Attendance</h3>

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
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                <td>{{ $record->employee->name ?? 'N/A' }}</td>
                <td>{{ $record->employee->job_title ?? '-' }}</td>
                <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('g:i A') : '-' }}</td>
                <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('g:i A') : '-' }}</td>
                <td><span class="badge bg-{{ $record->status === 'present' ? 'success' : 'secondary' }}">{{ ucfirst($record->status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No attendance records for this date.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
