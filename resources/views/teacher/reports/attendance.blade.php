@extends('layouts.app')
@section('title', 'Attendance Summary')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Attendance Summary</h3>
    <a href="{{ route('teacher.reports.index') }}" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
</div>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('teacher.reports.attendance') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small">Class / Section</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select a class</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}" @selected($sectionId == $s->id)>{{ $s->schoolClass->name }} — {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Filter</button>
        </div>
    </form>
</div>

@if($section)
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Excused</th>
                    <th>Days Marked</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->student->user->name }}</td>
                    <td><span class="badge bg-success">{{ $row->present }}</span></td>
                    <td><span class="badge bg-danger">{{ $row->absent }}</span></td>
                    <td><span class="badge bg-warning text-dark">{{ $row->late }}</span></td>
                    <td><span class="badge bg-secondary">{{ $row->excused }}</span></td>
                    <td>{{ $row->total_marked }}</td>
                    <td class="fw-semibold">{{ $row->rate !== null ? $row->rate.'%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No students in this class.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<p class="text-muted">Select a class to see its attendance summary.</p>
@endif
@endsection
