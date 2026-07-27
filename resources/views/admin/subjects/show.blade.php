@extends('layouts.app')
@section('title', $subject->name)
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Subjects</a>
    <h3 class="mb-0 mt-1">{{ $subject->name }}</h3>
    <span class="text-muted">{{ $subject->schoolClass->name ?? '—' }} @if($subject->code) &middot; Code: {{ $subject->code }} @endif</span>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Teachers teaching this subject</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Teacher</th><th>Class</th><th>Section</th></tr>
                    </thead>
                    <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td>
                                @if($a->teacher)
                                    <a href="{{ route('admin.teachers.show', $a->teacher) }}" class="text-decoration-none">{{ $a->teacher->user->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $a->schoolClass->name ?? '—' }}</td>
                            <td>{{ $a->section->name ?? 'All sections' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No teacher assigned to this subject yet. Assign one from the teacher's profile.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Performance by class</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Class</th><th>Average Score</th><th>Students Assessed</th><th>Results Recorded</th></tr>
                    </thead>
                    <tbody>
                    @forelse($performanceByClass as $perf)
                        <tr>
                            <td>{{ $perf['class']->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ $perf['average'] }}%</td>
                            <td>{{ $perf['students_assessed'] }}</td>
                            <td>{{ $perf['results_recorded'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No exam results recorded for this subject yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
