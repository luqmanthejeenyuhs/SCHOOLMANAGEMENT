@extends('layouts.app')
@section('title', 'Teacher Dashboard')
@section('content')
<h3 class="mb-1">Welcome, {{ auth()->user()->name }}</h3>
<p class="text-muted mb-3">Employee ID: {{ $teacher->employee_id ?? '—' }} @if($teacher->qualification) · {{ $teacher->qualification }} @endif</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Classes</span>
            <h4 class="mb-0">{{ $classSummaries->count() }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Subjects</span>
            <h4 class="mb-0">{{ $subjectCount }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Pupils Taught</span>
            <h4 class="mb-0">{{ $totalStudents }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Overall Average</span>
            <h4 class="mb-0">{{ $overallAverage !== null ? $overallAverage.'%' : '—' }}</h4>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">My Classes</h6>
                <a href="{{ route('teacher.subjects.index') }}" class="small text-decoration-none"><i class="bi bi-journal-text"></i> View by Subject instead</a>
            </div>
            <div class="row g-3">
                @forelse($classSummaries as $row)
                    <div class="col-md-6">
                        <div class="card h-100 p-3 {{ $row->is_main ? 'border-2' : '' }}" style="{{ $row->is_main ? 'border-color: var(--brand-gold);' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-semibold"><i class="bi bi-door-open"></i> {{ $row->section->schoolClass->name }} — {{ $row->section->name }}</div>
                                @if($row->is_main)<span class="badge" style="background: var(--brand-gold); color:#2b2107;">My Class</span>@endif
                            </div>
                            <div class="text-muted small mb-2">{{ $row->student_count }} pupil(s)</div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="small text-muted">Average:</span>
                                @if($row->average !== null)
                                    <span class="fw-bold {{ $row->average >= 50 ? 'text-success' : 'text-danger' }}">{{ $row->average }}%</span>
                                @else
                                    <span class="small text-muted">No results yet</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-auto">
                                <a href="{{ route('teacher.attendance.index', ['school_class_id' => $row->section->school_class_id, 'section_id' => $row->section->id]) }}" class="btn btn-sm btn-dark"><i class="bi bi-calendar-check"></i> Register</a>
                                <a href="{{ route('teacher.results.index', ['school_class_id' => $row->section->school_class_id]) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-clipboard-data"></i> Results</a>
                                <a href="{{ route('teacher.reports.index', ['school_class_id' => $row->section->school_class_id, 'section_id' => $row->section->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-bar-chart"></i> Reports</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-3">No classes assigned yet — ask the admin to attach you as a class teacher or subject teacher.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-calendar-day"></i> Today — {{ $todayName }}</h6>
                <a href="{{ route('teacher.timetable.index') }}" class="small text-decoration-none">Full timetable</a>
            </div>
            @if($todaySlots->isEmpty())
                <p class="text-muted small text-center py-3 mb-0">Nothing scheduled for today.</p>
            @else
                <ul class="list-unstyled mb-0">
                    @foreach($todaySlots as $slot)
                        <li class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="small fw-semibold">{{ $slot->displayLabel() }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('g:i A') }}–{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('g:i A') }}</div>
                            </div>
                            @if(in_array($slot->slot_type, ['break','lunch']))
                                <span class="badge bg-warning text-dark">{{ \App\Models\TimetableSlot::SLOT_TYPES[$slot->slot_type] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
