@extends('layouts.app')
@section('title', 'Sections')
@section('content')
<h3 class="mb-3">Sections</h3>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Section</h6>
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf
                <div class="mb-2">
                    <select name="school_class_id" class="form-select" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="e.g. A" required>
                </div>
                <button class="btn btn-dark w-100">Add Section</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Class</th><th>Section</th><th>Class Teacher</th><th></th></tr></thead>
                <tbody>
                @forelse($sections as $section)
                    <tr>
                        <td>{{ $section->schoolClass->name }}</td>
                        <td>{{ $section->name }}</td>
                        <td>{{ $section->classTeacher->user->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No sections yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
