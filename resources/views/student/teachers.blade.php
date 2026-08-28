@extends('layouts.app')
@section('title', 'My Teachers')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-person-workspace"></i> My Teachers</h3>
    <small class="text-muted">Who teaches what in {{ $student->schoolClass->name ?? 'your class' }}</small>
</div>

<div class="card p-3">
    <table class="table table-sm mb-0">
        <thead><tr><th>Subject</th><th>Teacher</th><th>Contact</th></tr></thead>
        <tbody>
        @forelse($subjectTeachers as $st)
            <tr>
                <td>{{ $st->subject->name ?? '—' }}</td>
                <td>{{ $st->teacher->user->name ?? '—' }}</td>
                <td>{{ $st->teacher->user->email ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-3">No subject teachers assigned to your class yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
