@extends('layouts.app')
@section('title', 'Students')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Students</h3>
    <a href="{{ route('admin.students.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Admit Student</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Admission No</th><th>Name</th><th>Class</th><th>Section</th><th>Guardian</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td><a href="{{ route('admin.students.show', $student) }}" class="fw-semibold">{{ $student->admission_no }}</a></td>
                    <td>{{ $student->user->name }}</td>
                    <td>{{ $student->schoolClass->name ?? '—' }}</td>
                    <td>{{ $student->section->name ?? '—' }}</td>
                    <td>{{ $student->guardian_name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this student?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No students yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $students->links() }}</div>
@endsection
