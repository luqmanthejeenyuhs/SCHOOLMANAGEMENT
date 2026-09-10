@extends('layouts.app')
@section('title', $student->user->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('teacher.classes.index') }}">My Classes</a></li>
        @if($student->section)
            <li class="breadcrumb-item"><a href="{{ route('teacher.classes.show', $student->section) }}">{{ $student->schoolClass->name }} — {{ $student->section->name }}</a></li>
        @endif
        <li class="breadcrumb-item active">{{ $student->user->name }}</li>
    </ol>
</nav>

<div class="card p-4 mb-3">
    <h4 class="mb-1">{{ $student->user->name }}</h4>
    <div class="text-muted">
        Admission No: {{ $student->admission_no }}
        &middot; {{ $student->schoolClass->name ?? '—' }} {{ $student->section->name ?? '' }}
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-3">
            <h6 class="mb-3">Recent Exam Results</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead>
                    <tbody>
                        @forelse($examResults as $result)
                        <tr>
                            <td>{{ $result->exam->name ?? '—' }}</td>
                            <td>{{ $result->subject->name ?? '—' }}</td>
                            <td>{{ $result->marks_obtained }}/{{ $result->max_marks }}</td>
                            <td>{{ $result->grade ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No results recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h6 class="mb-3">Recent Attendance</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @forelse($attendance as $record)
                        <tr>
                            <td>{{ $record->date->format('D, M j') }}</td>
                            <td><span class="badge bg-{{ match($record->status) { 'present' => 'success', 'late' => 'warning', 'absent' => 'danger', default => 'secondary' } }}">{{ ucfirst($record->status) }}</span></td>
                            <td>{{ $record->remarks ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No attendance recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
