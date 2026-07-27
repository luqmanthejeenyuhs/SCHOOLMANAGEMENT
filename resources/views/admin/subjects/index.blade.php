@extends('layouts.app')
@section('title', 'Subjects')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Subjects</h3>
    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal"><i class="bi bi-plus-lg"></i> Add Subject</button>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Class</th><th>Subject</th><th>Code</th><th></th></tr></thead>
        <tbody>
        @forelse($subjects as $subject)
            <tr>
                <td>{{ $subject->schoolClass->name }}</td>
                <td><a href="{{ route('admin.subjects.show', $subject) }}" class="text-decoration-none fw-semibold">{{ $subject->name }}</a></td>
                <td>{{ $subject->code ?? '—' }}</td>
                <td class="text-end">
                    <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this subject?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">No subjects yet. Click "Add Subject" to create one.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Add Subject modal --}}
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Class</label>
                        <select name="school_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Subject Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Physics" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Code (optional)</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. PHY">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
