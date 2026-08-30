@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<h3 class="mb-3">Reports</h3>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5><i class="bi bi-calendar-week"></i> Attendance Summary</h5>
            <p class="text-muted">Present/absent/late/excused counts and attendance rate per pupil, for a class over a date range.</p>
            <a href="{{ route('teacher.reports.attendance') }}" class="btn btn-dark align-self-start">Open Report</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5><i class="bi bi-bar-chart-line"></i> Exam Performance</h5>
            <p class="text-muted">Every pupil's marks across subjects for a chosen exam, with an overall average and class ranking.</p>
            <a href="{{ route('teacher.reports.exam-performance') }}" class="btn btn-dark align-self-start">Open Report</a>
        </div>
    </div>
</div>

<div class="card p-3 mt-4">
    <h6 class="mb-2">Quick Links</h6>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('teacher.results.index') }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-clipboard-data"></i> Enter/Add Exam Results</a>
        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-calendar-check"></i> Mark Register</a>
    </div>
</div>
@endsection
