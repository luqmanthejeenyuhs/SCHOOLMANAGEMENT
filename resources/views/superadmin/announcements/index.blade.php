@extends('layouts.app')
@section('title', 'Announcements')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-megaphone"></i> Announcements</h3>
    <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Announcement</a>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Title</th><th>Audience</th><th>Level</th><th>Status</th><th>Created</th><th style="width:160px;"></th></tr>
        </thead>
        <tbody>
        @forelse($announcements as $a)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $a->title }}</div>
                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($a->body, 80) }}</div>
                </td>
                <td>{{ $a->audience === 'all' ? 'All Schools' : count($a->school_ids ?? []).' school(s)' }}</td>
                <td><span class="badge {{ $a->level === 'warning' ? 'bg-warning text-dark' : ($a->level === 'success' ? 'bg-success' : 'bg-info text-dark') }}">{{ ucfirst($a->level) }}</span></td>
                <td>{{ $a->is_active ? 'Active' : 'Hidden' }}</td>
                <td class="small text-muted">{{ $a->created_at->format('d M Y') }}</td>
                <td class="d-flex gap-1">
                    <form method="POST" action="{{ route('superadmin.announcements.toggle-active', $a) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">{{ $a->is_active ? 'Hide' : 'Reactivate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.announcements.destroy', $a) }}" onsubmit="return confirm('Delete this announcement?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No announcements yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $announcements->links() }}</div>

@endsection
