@extends('layouts.app')
@section('title', 'Platform Audit Trail')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-clock-history"></i> Platform Audit Trail</h3>
        <small class="text-muted">Login activity across every school.</small>
    </div>
    <form method="GET" class="d-flex flex-wrap gap-2">
        <select name="school_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All schools</option>
            @foreach($schools as $s)
                <option value="{{ $s->id }}" @selected($schoolId == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="event" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All events</option>
            @foreach($events as $e)
                <option value="{{ $e }}" @selected($event === $e)>{{ ucwords(str_replace('_', ' ', $e)) }}</option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, username, or IP...">
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>When</th><th>School</th><th>Event</th><th>User</th><th>Username Attempted</th><th>IP Address</th></tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-nowrap small">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                <td>{{ $log->school?->name ?? '—' }}</td>
                <td>
                    @php $badgeClass = str_contains($log->event, 'failed') ? 'bg-danger' : (str_contains($log->event, 'logout') ? 'bg-secondary' : 'bg-success'); @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $log->event)) }}</span>
                </td>
                <td>{{ $log->user?->name ?? '—' }}</td>
                <td><code class="small">{{ $log->username_attempted ?? '—' }}</code></td>
                <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">No activity recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $logs->links() }}</div>

@endsection
