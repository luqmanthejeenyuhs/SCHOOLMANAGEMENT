@extends('layouts.app')
@section('title', $section->schoolClass->name.' - '.$section->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.show', $section->schoolClass) }}">{{ $section->schoolClass->name }}</a></li>
        <li class="breadcrumb-item active">{{ $section->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">{{ $section->schoolClass->name }} — {{ $section->name }}</h3>
        <span class="text-muted">
            {{ $students->count() }} student{{ $students->count() == 1 ? '' : 's' }}
            &middot; Class Teacher: {{ $section->classTeacher->user->name ?? 'Not assigned' }}
        </span>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Admit Student</a>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-info-btn" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab"><i class="bi bi-info-circle"></i> Information</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-students-btn" data-bs-toggle="tab" data-bs-target="#tab-students" type="button" role="tab"><i class="bi bi-people"></i> Students</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-timetable-btn" data-bs-toggle="tab" data-bs-target="#tab-timetable" type="button" role="tab"><i class="bi bi-calendar-week"></i> Timetable</button>
    </li>
</ul>

<div class="tab-content">

    {{-- INFORMATION --}}
    <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card p-3 text-center">
                    <span class="text-muted small">Class Teacher</span>
                    <h6 class="mb-0 mt-1">{{ $section->classTeacher->user->name ?? 'Not assigned' }}</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card p-3 text-center">
                    <span class="text-muted small">Total Students</span>
                    <h4 class="mb-0">{{ $students->count() }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card p-3 text-center">
                    <span class="text-muted small">Class Average</span>
                    <h4 class="mb-0">{{ $classAverage !== null ? $classAverage.'%' : '—' }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card p-3 text-center">
                    <span class="text-muted small">Subjects Taught</span>
                    <h4 class="mb-0">{{ $subjectTeachers->count() }}</h4>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <h6 class="mb-3">Subject Teachers &amp; Mean Grade</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Subject</th><th>Teacher</th><th>Mean Grade</th><th style="width:220px;"></th></tr></thead>
                <tbody>
                @forelse($subjectTeachers as $row)
                    <tr>
                        <td>{{ $row->subject->name ?? '—' }}</td>
                        <td>{{ $row->teacher->user->name ?? 'Not assigned' }}</td>
                        <td>
                            @if($row->mean_grade !== null)
                                <span class="fw-semibold {{ $row->mean_grade >= 50 ? 'text-success' : 'text-danger' }}">{{ $row->mean_grade }}%</span>
                                <span class="text-muted small">({{ $row->result_count }} result{{ $row->result_count == 1 ? '' : 's' }})</span>
                            @else
                                <span class="text-muted small">No results yet</span>
                            @endif
                        </td>
                        <td>
                            @if($row->mean_grade !== null)
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $row->mean_grade }}%; background: var(--brand-green);"></div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No subjects assigned to this class yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- STUDENTS --}}
    <div class="tab-pane fade" id="tab-students" role="tabpanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Admission No</th>
                            <th>Name</th>
                            <th>Guardian</th>
                            <th>Fee Balance</th>
                            <th>Last Exam Grade</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td><a href="{{ route('admin.students.show', ['student' => $student]) }}" class="fw-semibold">{{ $student->admission_no }}</a></td>
                            <td>{{ $student->user->name }}</td>
                            <td>{{ $student->guardian_name ?? '—' }}</td>
                            <td>
                                @if($student->fee_balance > 0)
                                    <span class="text-danger">KES {{ number_format($student->fee_balance, 2) }}</span>
                                @else
                                    <span class="text-success">Cleared</span>
                                @endif
                            </td>
                            <td>{{ $student->latestExamResult->grade ?? '—' }}</td>
                            <td>{{ $student->attendance_rate !== null ? $student->attendance_rate.'%' : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No students in this stream yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TIMETABLE --}}
    <div class="tab-pane fade" id="tab-timetable" role="tabpanel">
        @php $days = \App\Http\Controllers\Admin\TimetableController::DAYS; @endphp
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('admin.timetable.index', ['section_id' => $section->id]) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i> Edit Timetable</a>
        </div>
        @if($timetableSlots->isEmpty())
            <div class="card p-4 text-center text-muted">Nothing on this stream's timetable yet.</div>
        @else
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th style="width:120px;">Time</th>
                                @foreach($days as $day)
                                    <th>{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($timetableRanges as $range)
                            @php
                                [$start, $end] = explode('|', $range);
                                $daySlots = collect($days)->mapWithKeys(fn($day) => [$day => $timetableSlots->first(fn($s) => $s->day_of_week === $day && $s->start_time == $start && $s->end_time == $end)]);
                                $schoolWideEveryDay = $daySlots->every(fn($s) => $s && in_array($s->slot_type, \App\Models\TimetableSlot::SCHOOL_WIDE_TYPES))
                                    && $daySlots->pluck('slot_type')->unique()->count() === 1;
                            @endphp
                            <tr>
                                <td class="fw-semibold small">{{ \Carbon\Carbon::parse($start)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($end)->format('g:i A') }}</td>
                                @if($schoolWideEveryDay)
                                    <td colspan="{{ count($days) }}" class="bg-warning-subtle fw-semibold small">
                                        {{ $daySlots->first()->displayLabel() }}
                                    </td>
                                @else
                                    @foreach($days as $day)
                                        @php $slot = $daySlots[$day]; @endphp
                                        <td class="{{ $slot ? 'bg-light' : '' }}">
                                            @if($slot)
                                                <div class="fw-semibold small">{{ $slot->displayLabel() }}</div>
                                                @if($slot->slot_type === 'lesson')
                                                    <div class="text-muted small">{{ $slot->teacher->user->name ?? '' }}</div>
                                                @endif
                                                @if($slot->room)<div class="text-muted small">{{ $slot->room }}</div>@endif
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
