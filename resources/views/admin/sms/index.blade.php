@extends('layouts.app')
@section('title', 'Bulk SMS')
@section('content')
<h3 class="mb-3">Bulk SMS Alerts</h3>
<p class="text-muted small">Sends via Africa's Talking. Without a live <code>AT_API_KEY</code> in <code>.env</code>, messages are logged as "queued" so you can demo the full flow — plug in your sandbox or production key any time to start transmitting for real.</p>

<div class="card p-3 mb-4">
    <form method="POST" action="{{ route('admin.sms.send') }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Audience</label>
                <select name="audience" id="audienceSelect" class="form-select" required>
                    <option value="all">All Parents/Guardians</option>
                    <option value="class">Specific Class</option>
                    <option value="unpaid_balance">Guardians with Unpaid Fee Balance</option>
                </select>
            </div>
            <div class="col-md-3" id="classFieldWrap" style="display:none;">
                <label class="form-label small">Class</label>
                <select name="school_class_id" class="form-select">
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Category</label>
                <select name="category" class="form-select" required>
                    <option value="announcement">Announcement</option>
                    <option value="fee_reminder">Fee Reminder</option>
                    <option value="closure">School Closure</option>
                    <option value="general">General</option>
                </select>
            </div>
        </div>
        <div class="mt-2">
            <label class="form-label small">Message (max 480 characters)</label>
            <textarea name="message" class="form-control" rows="3" maxlength="480" required placeholder="e.g. Dear parent, kindly note school closes for half-term this Friday at 12pm."></textarea>
        </div>
        <button class="btn btn-dark mt-3">Send Broadcast</button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Phone</th><th>Student</th><th>Category</th><th>Message</th><th>Status</th><th>Sent</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->recipient_phone }}</td>
                    <td>{{ $log->student->user->name ?? '—' }}</td>
                    <td><span class="badge bg-secondary">{{ str_replace('_',' ', $log->category) }}</span></td>
                    <td class="small" style="max-width:300px;">{{ \Illuminate\Support\Str::limit($log->message, 60) }}</td>
                    <td>
                        @if($log->status === 'sent') <span class="badge bg-success">Sent</span>
                        @elseif($log->status === 'queued') <span class="badge bg-warning text-dark">Queued</span>
                        @else <span class="badge bg-danger">Failed</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No SMS sent yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>

<script>
const audienceSelect = document.getElementById('audienceSelect');
const classFieldWrap = document.getElementById('classFieldWrap');
function toggleClassField() {
    classFieldWrap.style.display = audienceSelect.value === 'class' ? '' : 'none';
}
audienceSelect.addEventListener('change', toggleClassField);
toggleClassField();
</script>
@endsection
