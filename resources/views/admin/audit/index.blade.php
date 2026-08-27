@extends('layouts.app')
@section('title', 'Audit Trail')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <nav style="--bs-breadcrumb-divider: '›';"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}" class="text-decoration-none">Settings</a></li>
            <li class="breadcrumb-item active">Audit Trail</li>
        </ol></nav>
        <h3 class="mb-0"><i class="bi bi-clock-history"></i> Audit Trail</h3>
        <small class="text-muted">Who signed in, when, and from which IP address — including failed attempts.</small>
    </div>
    <form method="GET" action="{{ route('admin.settings.audit_logs.index') }}" class="d-flex flex-wrap gap-2">
        <select name="event" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:170px;">
            <option value="">All events</option>
            @foreach($events as $e)
                <option value="{{ $e }}" {{ $event === $e ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $e)) }}</option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, username, or IP..." style="min-width:220px;">
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>When</th>
                <th>Event</th>
                <th>User</th>
                <th>Username</th>
                <th>IP Address</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="small text-nowrap">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                <td>
                    @php
                        $badgeClass = match(true) {
                            str_contains($log->event, 'failed') => 'bg-danger',
                            str_contains($log->event, 'logout') => 'bg-secondary',
                            default => 'bg-success',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $log->event)) }}</span>
                </td>
                <td>{{ $log->user?->name ?? '—' }}</td>
                <td><code class="small">{{ $log->username_attempted ?? '—' }}</code></td>
                <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
                <td class="small text-muted">{{ $log->description }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">No activity recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $logs->links() }}</div>

@endsection
