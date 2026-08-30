@extends('layouts.app')
@section('title', 'Teacher Dashboard')
@section('content')
<h3 class="mb-3">Welcome, {{ auth()->user()->name }}</h3>

<div class="card p-3 mb-4">
    <h6>Your Employee ID: {{ $teacher->employee_id ?? '—' }}</h6>
    <p class="text-muted small mb-0">Qualification: {{ $teacher->qualification ?? '—' }}</p>
</div>

<div class="card p-3">
    <h6 class="mb-3">My Classes</h6>
    <div class="row g-3">
        @forelse($sections as $section)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-3">
                    <div class="fw-semibold mb-1"><i class="bi bi-door-open"></i> {{ $section->schoolClass->name }} — {{ $section->name }}</div>
                    <div class="text-muted small mb-3">{{ $section->students()->count() }} pupil(s)</div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('teacher.attendance.index', ['school_class_id' => $section->school_class_id, 'section_id' => $section->id]) }}" class="btn btn-sm btn-dark"><i class="bi bi-calendar-check"></i> Register</a>
                        <a href="{{ route('teacher.results.index', ['school_class_id' => $section->school_class_id]) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-clipboard-data"></i> Results</a>
                        <a href="{{ route('teacher.reports.index', ['school_class_id' => $section->school_class_id, 'section_id' => $section->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-bar-chart"></i> Reports</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-3">No classes assigned yet — ask the admin to attach you as a class teacher or subject teacher.</div>
        @endforelse
    </div>
</div>
@endsection
