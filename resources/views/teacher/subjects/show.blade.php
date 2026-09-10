@extends('layouts.app')
@section('title', $subject->name)
@section('content')

<nav style="--bs-breadcrumb-divider: '›';"><ol class="breadcrumb mb-1">
    <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}" class="text-decoration-none">My Subjects</a></li>
    <li class="breadcrumb-item active">{{ $subject->name }}</li>
</ol></nav>
<h3 class="mb-3">{{ $subject->name }}</h3>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Overall Average</span>
            <h4 class="mb-0">{{ $overallAverage !== null ? $overallAverage.'%' : '—' }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Classes Taught</span>
            <h4 class="mb-0">{{ $byClass->count() }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Pupils With Results</span>
            <h4 class="mb-0">{{ $results->count() }}</h4>
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <h6 class="mb-3">By Class</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Class</th><th>Pupils</th><th>Average</th><th style="width:220px;"></th></tr></thead>
        <tbody>
        @forelse($byClass as $row)
            <tr>
                <td>{{ $row->label }}</td>
                <td>{{ $row->student_count }}</td>
                <td>{{ $row->average !== null ? $row->average.'%' : '—' }}</td>
                <td>
                    @if($row->average !== null)
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $row->average }}%; background: var(--brand-green);"></div>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No classes yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card p-3">
    <h6 class="mb-3">Every Pupil, Ranked</h6>
    @if($results->isEmpty())
        <p class="text-muted text-center py-3 mb-0">No results recorded for this subject yet.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Pupil</th><th>Class</th><th>Average in {{ $subject->name }}</th></tr></thead>
            <tbody>
            @foreach($results as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->student->user->name ?? '—' }}</td>
                    <td>{{ $row->student->schoolClass->name ?? '—' }}</td>
                    <td class="{{ $row->average >= 50 ? 'text-success' : 'text-danger' }} fw-semibold">{{ $row->average }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
