@extends('layouts.app')
@section('title', 'Exams')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Exams &amp; Results</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.grading_scales.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders"></i> Grading Scale</a>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createExamModal"><i class="bi bi-plus-lg"></i> Create Exam</button>
    </div>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Exam</th><th>Class</th><th>Term</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($exams as $exam)
            <tr>
                <td>{{ $exam->name }}</td>
                <td>{{ $exam->schoolClass->name }}</td>
                <td>{{ $exam->term ?? '—' }}</td>
                <td>{{ $exam->exam_date?->format('d M Y') ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.exams.results', $exam) }}" class="btn btn-sm btn-outline-primary">View Results</a>
                    <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No exams yet. Click "Create Exam" to add one.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Create Exam modal --}}
<div class="modal fade" id="createExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.exams.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Exam Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Mid-Term Exam" required>
                    </div>
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
                        <label class="form-label small">Term (optional)</label>
                        <input type="text" name="term" class="form-control" placeholder="e.g. Term 2">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Exam Date (optional)</label>
                        <input type="date" name="exam_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Create Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
