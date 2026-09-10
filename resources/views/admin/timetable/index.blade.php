@extends('layouts.app')
@section('title', 'Timetable')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Timetable</h3>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" action="{{ route('admin.timetable.index') }}" class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">Viewing:</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                @forelse($sections as $s)
                    <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->schoolClass->name }} {{ $s->name }}</option>
                @empty
                    <option value="">No streams yet</option>
                @endforelse
            </select>
        </form>
        @if($section)
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addSlotModal"><i class="bi bi-plus-lg"></i> Add to Timetable</button>
        @endif
    </div>
</div>

@if(! $section)
    <div class="card p-4 text-center text-muted">Create a class and a stream first (under Classes), then come back here to build its timetable.</div>
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
                @forelse($timeRanges as $range)
                    @php
                        [$start, $end] = explode('|', $range);
                        // A break/lunch usually applies at the same time every
                        // day — if every weekday has the identical school-wide
                        // slot at this time, show it as a single spanning row
                        // instead of repeating it five times.
                        $daySlots = collect($days)->mapWithKeys(fn($day) => [$day => $slots->first(fn($s) => $s->day_of_week === $day && $s->start_time == $start && $s->end_time == $end)]);
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
                                            <div class="text-muted small">{{ $slot->teacher->user->name }}</div>
                                        @elseif($slot->teacher)
                                            <div class="text-muted small">{{ $slot->teacher->user->name }}</div>
                                        @endif
                                        @if($slot->room)<div class="text-muted small">{{ $slot->room }}</div>@endif
                                        <form action="{{ route('admin.timetable.destroy', $slot) }}" method="POST" onsubmit="return confirm('Remove this from the timetable?');" class="mt-1">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ count($days) + 1 }}" class="text-center text-muted py-4">Nothing scheduled yet for {{ $section->schoolClass->name }} {{ $section->name }}. Click "Add to Timetable" to start building it.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add to Timetable Modal -->
    <div class="modal fade" id="addSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.timetable.store') }}">
                    @csrf
                    <input type="hidden" name="viewing_section_id" value="{{ $section->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add to Timetable — {{ $section->schoolClass->name }} {{ $section->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Type</label>
                            <select name="slot_type" id="slotTypeSelect" class="form-select" required>
                                @foreach($slotTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text" id="schoolWideHint" style="display:none;">Break/Lunch applies school-wide — it'll show on every class's and every teacher's timetable at this time, not just {{ $section->schoolClass->name }} {{ $section->name }}.</div>
                        </div>

                        <div id="lessonFields">
                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                            <div class="mb-2">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" id="subjectSelect" class="form-select">
                                    <option value="">Select subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @if($subjects->isEmpty())<div class="form-text text-warning">No subjects set up for this class yet.</div>@endif
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id" id="teacherSelect" class="form-select">
                                    <option value="">Select subject first</option>
                                </select>
                                @if($assignments->isEmpty())<div class="form-text text-warning">No teacher is assigned to teach this class yet.</div>@endif
                            </div>
                        </div>

                        <div id="personalFields" style="display:none;">
                            <div class="mb-2">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id_personal" id="personalTeacherSelect" class="form-select">
                                    <option value="">Select teacher</option>
                                    @foreach($allTeachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->user->name ?? 'Teacher #'.$t->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Label (optional)</label>
                                <input type="text" name="label_personal" id="personalLabel" class="form-control" placeholder="e.g. Basketball Club, Exam Invigilation">
                            </div>
                        </div>

                        <div id="schoolWideFields" style="display:none;">
                            <div class="mb-2">
                                <label class="form-label">Label (optional)</label>
                                <input type="text" name="label_wide" id="wideLabel" class="form-control" placeholder="Defaults to Break Time / Lunch Break">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-0">Start</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-0">End</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-2" id="roomField">
                            <label class="form-label">Room (optional)</label>
                            <input type="text" name="room" class="form-control" placeholder="e.g. Room 4">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-dark">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Subject -> Teacher is filtered client-side from this section's actual
    // class_subject_teacher assignments, so you can only pick a teacher who
    // is really assigned to teach that subject to this class.
    const assignments = @json($assignments->map(fn($a) => ['subject_id' => $a->subject_id, 'teacher_id' => $a->teacher_id, 'teacher_name' => $a->teacher->user->name]));
    const schoolWideTypes = @json(\App\Models\TimetableSlot::SCHOOL_WIDE_TYPES);

    document.getElementById('subjectSelect')?.addEventListener('change', function () {
        const teacherSelect = document.getElementById('teacherSelect');
        const matches = assignments.filter(a => a.subject_id == this.value);
        teacherSelect.innerHTML = '<option value="">Select teacher</option>';
        matches.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.teacher_id;
            opt.textContent = a.teacher_name;
            teacherSelect.appendChild(opt);
        });
        if (matches.length === 0) {
            teacherSelect.innerHTML = '<option value="">No teacher assigned to this subject for this class</option>';
        }
    });

    // Swap which fields are visible/required based on the chosen type, and
    // rename the personal/school-wide inputs to the real field names the
    // controller expects only when they're actually the active set — so a
    // hidden field never overrides a visible one.
    document.getElementById('slotTypeSelect').addEventListener('change', function () {
        const type = this.value;
        const isLesson = type === 'lesson';
        const isSchoolWide = schoolWideTypes.includes(type);
        const isPersonal = !isLesson && !isSchoolWide;

        document.getElementById('lessonFields').style.display = isLesson ? '' : 'none';
        document.getElementById('personalFields').style.display = isPersonal ? '' : 'none';
        document.getElementById('schoolWideFields').style.display = isSchoolWide ? '' : 'none';
        document.getElementById('schoolWideHint').style.display = isSchoolWide ? '' : 'none';
        document.getElementById('roomField').style.display = isSchoolWide ? 'none' : '';

        document.getElementById('subjectSelect').required = isLesson;
        document.getElementById('teacherSelect').required = isLesson;

        const personalTeacher = document.getElementById('personalTeacherSelect');
        const personalLabel = document.getElementById('personalLabel');
        const wideLabel = document.getElementById('wideLabel');
        personalTeacher.name = isPersonal ? 'teacher_id' : 'teacher_id_personal';
        personalTeacher.required = isPersonal;
        personalLabel.name = isPersonal ? 'label' : 'label_personal';
        wideLabel.name = isSchoolWide ? 'label' : 'label_wide';
    });
    document.getElementById('slotTypeSelect').dispatchEvent(new Event('change'));
    </script>

    @if($errors->any())
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('addSlotModal')).show();
    });
    </script>
    @endif
@endif
@endsection
