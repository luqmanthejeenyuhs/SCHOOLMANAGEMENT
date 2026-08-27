@extends('layouts.app')
@section('title', 'Schools')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Schools</h3>
    <a href="{{ route('superadmin.schools.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add School</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>School</th>
                    <th>Subdomain</th>
                    <th>Users</th>
                    <th>Students</th>
                    <th>Teachers</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($schools as $school)
                <tr>
                    <td class="fw-semibold">{{ $school->name }}</td>
                    <td><code>{{ $school->slug }}</code></td>
                    <td>{{ $school->users_count }}</td>
                    <td>{{ $school->students_count }}</td>
                    <td>{{ $school->teachers_count }}</td>
                    <td>
                        @if($school->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Suspended</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('superadmin.schools.impersonate', $school) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">Impersonate</button>
                        </form>
                        <a href="{{ route('superadmin.schools.edit', $school) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                        <form method="POST" action="{{ route('superadmin.schools.toggle-active', $school) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning">{{ $school->is_active ? 'Suspend' : 'Reactivate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('superadmin.schools.destroy', $school) }}" class="d-inline" onsubmit="return confirm('This permanently deletes {{ $school->name }} and ALL of its data (students, invoices, payments, everything). This cannot be undone. Continue?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No schools onboarded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
