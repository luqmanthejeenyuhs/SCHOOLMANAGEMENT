@extends('layouts.app')
@section('title', $section->schoolClass->name.' - '.$section->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('teacher.classes.index') }}">My Classes</a></li>
        <li class="breadcrumb-item active">{{ $section->schoolClass->name }} — {{ $section->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">
            {{ $section->schoolClass->name }} — {{ $section->name }}
            @if($isClassTeacher)<span class="badge bg-warning text-dark ms-2"><i class="bi bi-star-fill"></i> Class Teacher</span>@endif
        </h3>
        <span class="text-muted">Class Teacher: {{ $section->classTeacher->user->name ?? 'Not assigned' }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('teacher.attendance.index', ['school_class_id' => $section->school_class_id, 'section_id' => $section->id]) }}" class="btn btn-dark btn-sm"><i class="bi bi-calendar-check"></i> Mark / Register Attendance</a>
        <a href="{{ route('teacher.results.index', ['school_class_id' => $section->school_class_id]) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-clipboard-data"></i> Enter Results</a>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="classTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button">Details</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers-pane" type="button">Teachers</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button">Attendance</button>
    </li>
</ul>

<div class="tab-content">
    {{-- DETAILS --}}
    <div class="tab-pane fade show active" id="details-pane" role="tabpanel">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-4 fw-bold">{{ $students->count() }}</div>
                    <div class="text-muted small">Students</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-4 fw-bold">{{ $transferredIn }}</div>
                    <div class="text-muted small">Transferred In</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-4 fw-bold">{{ $transferredOut }}</div>
                    <div class="text-muted small">Transferred Out</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-4 fw-bold">{{ $averagePerformance !== null ? round($averagePerformance, 1).'%' : '—' }}</div>
                    <div class="text-muted small">Average Performance</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Admission No</th>
                            <th>Name</th>
                            <th>Last Exam Grade</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td><a href="{{ route('teacher.students.show', $student) }}" class="fw-semibold">{{ $student->admission_no }}</a></td>
                            <td>{{ $student->user->name }}</td>
                            <td>{{ $student->latestExamResult->grade ?? '—' }}</td>
                            <td>{{ $student->attendance_rate !== null ? $student->attendance_rate.'%' : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No students in this stream yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TEACHERS --}}
    <div class="tab-pane fade" id="teachers-pane" role="tabpanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Subject</th><th>Teacher</th></tr>
                    </thead>
                    <tbody>
                        @forelse($subjectTeachers as $assignment)
                        <tr>
                            <td>{{ $assignment->subject->name ?? '—' }}</td>
                            <td>
                                {{ $assignment->teacher->user->name ?? '—' }}
                                @if($assignment->teacher && $assignment->teacher->user_id === auth()->id())
                                    <span class="badge bg-success ms-1">You</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4">No subject teachers assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ATTENDANCE --}}
    <div class="tab-pane fade" id="attendance-pane" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Last {{ $recentDates->count() }} marked day(s)</div>
            <a href="{{ route('teacher.attendance.index', ['school_class_id' => $section->school_class_id, 'section_id' => $section->id]) }}" class="btn btn-sm btn-dark"><i class="bi bi-calendar-check"></i> Mark / Register Attendance</a>
        </div>

        @if($recentDates->isEmpty())
            <div class="card p-4 text-center text-muted">No attendance has been marked for this class yet.</div>
        @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            @foreach($recentDates as $date)
                                <th class="text-center">{{ \Illuminate\Support\Carbon::parse($date)->format('D, M j') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->user->name }}</td>
                            @foreach($recentDates as $date)
                                @php
                                    $record = ($attendanceGrid->get($student->id) ?? collect())
                                        ->first(fn ($r) => $r->date->format('Y-m-d') === \Illuminate\Support\Carbon::parse($date)->format('Y-m-d'));
                                    $badge = match($record?->status) {
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'absent' => 'danger',
                                        'excused' => 'secondary',
                                        default => 'light text-muted',
                                    };
                                @endphp
                                <td class="text-center">
                                    <span class="badge bg-{{ $badge }}">{{ $record ? ucfirst($record->status) : '—' }}</span>
                                </td>
                            @endforeach
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
