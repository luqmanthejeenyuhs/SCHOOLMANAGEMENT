@extends('layouts.app')
@section('title', 'Teachers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Teachers</h3>
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Teacher</a>
</div>

<form method="GET" action="{{ route('admin.teachers.index') }}" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-8">
            <label class="form-label mb-1 small text-muted">Search by teacher name or employee/staff number</label>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="e.g. Jane Wanjiku or EMP-1023">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
        </div>
        @if($search)
        <div class="col-md-2">
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        @endif
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Qualification</th><th>Joined</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                <tr>
                    <td><a href="{{ route('admin.teachers.show', $teacher) }}" class="fw-semibold text-decoration-none">{{ $teacher->employee_id }}</a></td>
                    <td><a href="{{ route('admin.teachers.show', $teacher) }}" class="text-decoration-none">{{ $teacher->user->name }}</a></td>
                    <td>{{ $teacher->user->email }}</td>
                    <td>{{ $teacher->qualification ?? '—' }}</td>
                    <td>{{ $teacher->joining_date?->format('d M Y') ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this teacher?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No teachers found{{ $search ? ' for "'.$search.'"' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $teachers->links() }}</div>
@endsection
