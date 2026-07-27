@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<h3 class="mb-3">Take Attendance</h3>

<style>
    .status-group { display: flex; gap: 6px; flex-wrap: wrap; }
    .status-group .btn { flex: 1 1 auto; min-width: 74px; padding: .55rem .5rem; font-size: .82rem; font-weight: 600; }
    .student-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; flex-wrap: wrap; border-bottom: 1px solid #eef0f4; }
    .student-row:last-child { border-bottom: none; }
    .student-meta { min-width: 160px; }
    .student-meta .name { font-weight: 600; }
    .student-meta .adm { color: #6c757d; font-size: .8rem; }
    .save-bar { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e5e7eb; padding: 10px 14px; margin: 0 -1px; box-shadow: 0 -4px 14px rgba(0,0,0,.06); z-index: 5; }
    .count-pill { font-size: .78rem; padding: .3rem .6rem; }
    @media (max-width: 576px) {
        .student-row { flex-direction: column; align-items: stretch; }
        .status-group .btn { flex: 1 1 45%; }
    }
</style>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('teacher.attendance.index') }}" class="row g-2 align-items-end">
        <div class="col-6 col-md-4">
            <label class="form-label small">Class</label>
            <select name="school_class_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        @if($classId && $sections->count())
        <div class="col-6 col-md-3">
            <label class="form-label small">Section</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                <option value="">All sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" @selected($sectionId == $section->id)>{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-6 col-md-3">
            <label class="form-label small">Date</label>
            <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($classId)
<form method="POST" action="{{ route('teacher.attendance.store') }}" id="attendanceForm">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    @if($students->count())
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
        <div class="d-flex gap-2">
            <span class="badge bg-success count-pill">Present: <span id="countPresent">0</span></span>
            <span class="badge bg-danger count-pill">Absent: <span id="countAbsent">0</span></span>
            <span class="badge bg-warning text-dark count-pill">Late: <span id="countLate">0</span></span>
            <span class="badge bg-secondary count-pill">Excused: <span id="countExcused">0</span></span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
            <i class="bi bi-check2-all"></i> Mark all present
        </button>
    </div>
    @endif

    <div class="card">
        @forelse($students as $student)
            @php $existing = $student->attendances->first()?->status ?? 'present'; @endphp
            <div class="student-row">
                <div class="student-meta">
                    <div class="name">{{ $student->user->name }}</div>
                    <div class="adm">Adm No: {{ $student->admission_no }}</div>
                </div>
                <div class="status-group btn-group" role="group" data-student="{{ $student->id }}">
                    @foreach(['present' => ['Present','success'], 'absent' => ['Absent','danger'], 'late' => ['Late','warning'], 'excused' => ['Excused','secondary']] as $value => [$label, $color])
                        <input type="radio" class="btn-check status-radio" name="statuses[{{ $student->id }}]"
                               id="s{{ $student->id }}_{{ $value }}" value="{{ $value }}" autocomplete="off"
                               @checked($existing === $value)>
                        <label class="btn btn-outline-{{ $color }}" for="s{{ $student->id }}_{{ $value }}">{{ $label }}</label>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">No students in this class.</div>
        @endforelse
    </div>

    @if($students->count())
    <div class="save-bar text-end">
        <button class="btn btn-dark px-4"><i class="bi bi-check-lg"></i> Save Attendance</button>
    </div>
    @endif
</form>

<script>
    function updateCounts() {
        const counts = { present: 0, absent: 0, late: 0, excused: 0 };
        document.querySelectorAll('.status-radio:checked').forEach(el => {
            if (counts[el.value] !== undefined) counts[el.value]++;
        });
        document.getElementById('countPresent').textContent = counts.present;
        document.getElementById('countAbsent').textContent = counts.absent;
        document.getElementById('countLate').textContent = counts.late;
        document.getElementById('countExcused').textContent = counts.excused;
    }

    function markAll(status) {
        document.querySelectorAll('.status-group').forEach(group => {
            const radio = group.querySelector(`input[value="${status}"]`);
            if (radio) radio.checked = true;
        });
        updateCounts();
    }

    document.querySelectorAll('.status-radio').forEach(el => el.addEventListener('change', updateCounts));
    updateCounts();
</script>
@else
<p class="text-muted">Select a class to take attendance.</p>
@endif
@endsection
