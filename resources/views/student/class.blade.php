@extends('layouts.app')
@section('title', 'My Class')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-people"></i> My Class</h3>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Class</span>
            <h5 class="mb-0">{{ $student->schoolClass->name ?? '—' }}</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Section / Stream</span>
            <h5 class="mb-0">{{ $student->section->name ?? '—' }}</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Classmates</span>
            <h5 class="mb-0">{{ $classmateCount ?? '—' }}</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Class Teacher</span>
            <h5 class="mb-0">{{ $student->section->classTeacher->user->name ?? '—' }}</h5>
        </div>
    </div>
</div>

<div class="card p-3">
    <h6 class="mb-3">Weekly Timetable</h6>
    @if($timetable->isEmpty())
        <p class="text-muted text-center py-3 mb-0">No timetable has been published for your class yet.</p>
    @else
        @foreach($timetable as $day => $slots)
            <h6 class="small text-uppercase text-muted mt-3 mb-2" style="letter-spacing:.05em;">{{ $day }}</h6>
            <table class="table table-sm mb-2">
                <thead><tr><th style="width:160px;">Time</th><th>Subject</th><th>Teacher</th><th>Room</th></tr></thead>
                <tbody>
                @foreach($slots as $slot)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('g:i A') }}</td>
                        <td>{{ $slot->subject->name ?? '—' }}</td>
                        <td>{{ $slot->teacher->user->name ?? '—' }}</td>
                        <td>{{ $slot->room ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</div>

@endsection
