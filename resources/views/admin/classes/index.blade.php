@extends('layouts.app')
@section('title', 'Classes & Activities')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Classes &amp; Activities</h3>
    <div class="d-flex gap-2 flex-wrap">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="d-flex" style="min-width:260px;">
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search classes, streams or teachers...">
            <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addClassModal"><i class="bi bi-plus-lg"></i> Add Class</button>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addStreamModal"><i class="bi bi-plus-lg"></i> Add Stream</button>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.classes.index') }}"><i class="bi bi-building"></i> Classes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.activities.index') }}"><i class="bi bi-trophy"></i> Activities</a>
    </li>
</ul>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Class</th><th>Stream</th><th>Class Teacher</th><th>Total Students</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($sections as $section)
            <tr>
                <td><a href="{{ route('admin.classes.show', $section->schoolClass) }}" class="text-decoration-none">{{ $section->schoolClass->name }}</a></td>
                <td>
                    <a href="{{ route('admin.sections.show', $section) }}" class="fw-semibold text-decoration-none">
                        {{ $section->schoolClass->name }} {{ $section->name }}
                    </a>
                </td>
                <td>{{ $section->classTeacher->user->name ?? '—' }}</td>
                <td>{{ $section->students_count }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this stream?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">{{ $search ? 'No classes match your search.' : 'No streams yet. Use "Add Class" and "Add Stream" above.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($classes->count())
<div class="card mt-3 p-3">
    <h6 class="mb-2">All Grades</h6>
    <div class="d-flex flex-wrap gap-2">
        @foreach($classes as $class)
            <div class="d-flex align-items-center border rounded-pill ps-3 pe-1 py-1">
                <a href="{{ route('admin.classes.show', $class) }}" class="text-decoration-none me-2">
                    {{ $class->name }} <span class="badge bg-secondary">{{ $class->students_count }}</span>
                </a>
                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Delete this class?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Add Class modal --}}
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.classes.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">A grade/level, e.g. "Grade 9".</p>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Grade 11" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Stream modal --}}
<div class="modal fade" id="addStreamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Stream</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">A parallel class within a grade, e.g. "Green" or "A", with its class teacher.</p>
                    <div class="mb-2">
                        <select name="school_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Stream, e.g. Green" required>
                    </div>
                    <div class="mb-2">
                        <select name="class_teacher_id" class="form-select">
                            <option value="">Class teacher (optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Stream</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
