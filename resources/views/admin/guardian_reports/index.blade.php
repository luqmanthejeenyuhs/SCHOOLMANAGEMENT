@extends('layouts.app')
@section('title', 'Parent Reports')
@section('content')
@php
    $scope = old('scope', $prefill['scope']);
    $types = old('types', ['attendance', 'results']);
    $channels = old('channels', ['sms', 'email']);
    $preview = session('preview');
@endphp

<h3 class="mb-1">Parent Reports</h3>
<p class="text-muted small">
    Send guardians their child's attendance, exam results and fee balance by SMS and/or email — for one learner,
    a stream, or a whole class. Guardians are taken from the parent accounts linked to each learner, plus the guardian
    phone saved on the learner's profile. Without an <code>AT_API_KEY</code> SMS are logged as "queued"; email uses your <code>MAIL_*</code> settings.
</p>

<div class="card p-3 mb-4">
    <form method="POST" action="{{ route('admin.guardian_reports.send') }}" id="reportForm">
        @csrf

        {{-- 1. WHO --}}
        <h6 class="text-uppercase text-muted small mb-2">1. Whose guardians?</h6>
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Send for</label>
                <select name="scope" id="scopeSelect" class="form-select">
                    <option value="class" @selected($scope === 'class')>A whole class</option>
                    <option value="section" @selected($scope === 'section')>A stream</option>
                    <option value="student" @selected($scope === 'student')>One learner</option>
                </select>
            </div>
            <div class="col-md-4 scope-field" data-scope="class">
                <label class="form-label small">Class</label>
                <select name="school_class_id" class="form-select">
                    <option value="">Select class</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected(old('school_class_id', $prefill['school_class_id']) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 scope-field" data-scope="section">
                <label class="form-label small">Stream</label>
                <select name="section_id" class="form-select">
                    <option value="">Select stream</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" @selected(old('section_id', $prefill['section_id']) == $s->id)>{{ $s->schoolClass->name }} {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 scope-field" data-scope="student">
                <label class="form-label small">Admission number</label>
                <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no', $prefill['admission_no']) }}" placeholder="e.g. ADM-1032">
            </div>
        </div>

        {{-- 2. WHAT --}}
        <h6 class="text-uppercase text-muted small mb-2">2. What to include</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="types[]" value="attendance" id="tAtt" @checked(in_array('attendance', $types))>
                    <label class="form-check-label" for="tAtt"><i class="bi bi-calendar-check"></i> Attendance</label>
                </div>
                <div class="row g-2 mt-1" id="attendanceFields">
                    <div class="col-6"><label class="form-label small mb-0">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ old('from', now()->subDays(30)->toDateString()) }}"></div>
                    <div class="col-6"><label class="form-label small mb-0">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ old('to', now()->toDateString()) }}"></div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="types[]" value="results" id="tRes" @checked(in_array('results', $types))>
                    <label class="form-check-label" for="tRes"><i class="bi bi-journal-check"></i> Exam results (with class position)</label>
                </div>
                <div class="mt-1" id="resultsFields">
                    <label class="form-label small mb-0">Exam</label>
                    <select name="exam_id" class="form-select form-select-sm">
                        <option value="">Select exam</option>
                        @foreach($exams as $e)
                            <option value="{{ $e->id }}" @selected(old('exam_id') == $e->id)>
                                {{ $e->name }}@if($e->term) — {{ $e->term }}@endif @if($e->schoolClass) ({{ $e->schoolClass->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="types[]" value="fees" id="tFee" @checked(in_array('fees', $types))>
                    <label class="form-check-label" for="tFee"><i class="bi bi-cash-coin"></i> Fee balance</label>
                </div>
                <div class="form-text">Off by default — fee details are sensitive.</div>
            </div>
        </div>

        {{-- 3. HOW --}}
        <h6 class="text-uppercase text-muted small mb-2">3. How to send</h6>
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="channels[]" value="sms" id="cSms" @checked(in_array('sms', $channels))><label class="form-check-label" for="cSms"><i class="bi bi-chat-dots"></i> SMS</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="channels[]" value="email" id="cEmail" @checked(in_array('email', $channels))><label class="form-check-label" for="cEmail"><i class="bi bi-envelope"></i> Email</label></div>
            </div>
            <div class="col-md-9">
                <label class="form-label small mb-0">Personal note (optional, max 200 characters — added to the report)</label>
                <input type="text" name="note" class="form-control" maxlength="200" value="{{ old('note') }}" placeholder="e.g. Parents' meeting on Friday 17th at 9am.">
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" formaction="{{ route('admin.guardian_reports.preview') }}" class="btn btn-outline-dark"><i class="bi bi-eye"></i> Preview</button>
            <button type="submit" class="btn btn-dark" onclick="return confirm('Send these reports to guardians now?');"><i class="bi bi-send"></i> Send reports</button>
        </div>
    </form>
</div>

@if($preview)
    <div class="card p-3 mb-4 border-info">
        <h6 class="mb-2"><i class="bi bi-eye"></i> Preview — nothing has been sent</h6>
        <div class="row g-3 text-center mb-3">
            <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $preview['students'] }}</div><div class="small text-muted">Learners</div></div>
            <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $preview['with_sms'] }}</div><div class="small text-muted">With a phone number</div></div>
            <div class="col-6 col-md-3"><div class="fs-4 fw-bold">{{ $preview['with_email'] }}</div><div class="small text-muted">With an email address</div></div>
            <div class="col-6 col-md-3"><div class="fs-4 fw-bold {{ $preview['no_contact'] ? 'text-danger' : '' }}">{{ $preview['no_contact'] }}</div><div class="small text-muted">No guardian contact</div></div>
        </div>
        <div class="small text-muted mb-1">Sample SMS (for {{ $preview['sample_name'] }}):</div>
        <div class="p-2 bg-light border rounded small">{{ $preview['sample_sms'] }}</div>
        <div class="small text-muted mt-1">{{ strlen($preview['sample_sms']) }} characters. Emails carry the full per-subject breakdown.</div>
    </div>
@endif

<h6 class="mb-2">Recently sent</h6>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Learner</th><th>Guardian</th><th>Channel</th><th>Sent to</th><th>Included</th><th>Status</th><th>When</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->student->user->name ?? '—' }}</td>
                    <td>{{ $log->recipient_name ?? '—' }}</td>
                    <td><span class="badge bg-secondary">{{ strtoupper($log->channel) }}</span></td>
                    <td class="small">{{ $log->recipient }}</td>
                    <td class="small text-capitalize">{{ str_replace(',', ', ', $log->report_types) }}</td>
                    <td>
                        @if($log->status === 'sent') <span class="badge bg-success">Sent</span>
                        @elseif($log->status === 'queued') <span class="badge bg-warning text-dark" title="{{ $log->error }}">Queued</span>
                        @else <span class="badge bg-danger" title="{{ $log->error }}">Failed</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No reports sent yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>

<script>
(function () {
    const scopeSelect = document.getElementById('scopeSelect');
    function syncScope() {
        document.querySelectorAll('.scope-field').forEach(el => {
            el.style.display = el.dataset.scope === scopeSelect.value ? '' : 'none';
        });
    }
    function bindToggle(checkboxId, fieldsId) {
        const cb = document.getElementById(checkboxId), box = document.getElementById(fieldsId);
        const sync = () => { box.style.display = cb.checked ? '' : 'none'; };
        cb.addEventListener('change', sync); sync();
    }
    scopeSelect.addEventListener('change', syncScope); syncScope();
    bindToggle('tAtt', 'attendanceFields');
    bindToggle('tRes', 'resultsFields');
})();
</script>
@endsection
