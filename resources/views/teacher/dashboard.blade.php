@extends('layouts.app')
@section('title', 'Teacher Dashboard')
@section('content')
<h3 class="mb-3">Welcome, {{ auth()->user()->name }}</h3>

<div class="card p-3 mb-4">
    <h6>Your Employee ID: {{ $teacher->employee_id ?? '—' }}</h6>
    <p class="text-muted small mb-0">Qualification: {{ $teacher->qualification ?? '—' }}</p>
</div>

<div class="card p-3">
    <h6>Your Assigned Classes &amp; Subjects</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Class</th><th>Section</th><th>Subject</th></tr></thead>
        <tbody>
        @forelse($teacher->assignments ?? [] as $assignment)
            <tr>
                <td>{{ $assignment->schoolClass->name }}</td>
                <td>{{ $assignment->section->name ?? '—' }}</td>
                <td>{{ $assignment->subject->name }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-3">No assignments yet — ask the admin to assign you a class/subject.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="{{ route('teacher.attendance.index') }}" class="btn btn-dark me-2"><i class="bi bi-calendar-check"></i> Take Attendance</a>
    <a href="{{ route('teacher.results.index') }}" class="btn btn-outline-dark"><i class="bi bi-clipboard-data"></i> Enter Exam Results</a>
</div>
@endsection
