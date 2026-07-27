@extends('layouts.app')
@section('title', $section->schoolClass->name.' - '.$section->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.show', $section->schoolClass) }}">{{ $section->schoolClass->name }}</a></li>
        <li class="breadcrumb-item active">{{ $section->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">{{ $section->schoolClass->name }} — {{ $section->name }}</h3>
        <span class="text-muted">
            {{ $students->count() }} student{{ $students->count() == 1 ? '' : 's' }}
            &middot; Class Teacher: {{ $section->classTeacher->user->name ?? 'Not assigned' }}
        </span>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Admit Student</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Guardian</th>
                    <th>Fee Balance</th>
                    <th>Exam Average</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td><a href="{{ route('admin.students.show', $student) }}" class="fw-semibold">{{ $student->admission_no }}</a></td>
                    <td>{{ $student->user->name }}</td>
                    <td>{{ $student->guardian_name ?? '—' }}</td>
                    <td>
                        @if($student->fee_balance > 0)
                            <span class="text-danger">KES {{ number_format($student->fee_balance, 2) }}</span>
                        @else
                            <span class="text-success">Cleared</span>
                        @endif
                    </td>
                    <td>{{ $student->exam_average !== null ? $student->exam_average.'%' : '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-secondary">View Profile</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No students in this stream yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
