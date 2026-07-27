@extends('layouts.app')
@section('title', 'Enter Results')
@section('content')
<h3 class="mb-3">Enter Exam Results</h3>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('teacher.results.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small">Exam</label>
            <select name="exam_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select exam</option>
                @foreach($exams as $e)
                    <option value="{{ $e->id }}" @selected($examId == $e->id)>{{ $e->name }} — {{ $e->schoolClass->name }}</option>
                @endforeach
            </select>
        </div>
        @if($exam)
        <div class="col-md-4">
            <label class="form-label small">Subject</label>
            <select name="subject_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select subject</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </form>
</div>

@if($exam && $subjectId)
<form method="POST" action="{{ route('teacher.results.store') }}">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $examId }}">
    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
    <div class="card p-3 mb-3" style="max-width:200px;">
        <label class="form-label small">Max Marks</label>
        <input type="number" name="max_marks" id="max_marks" class="form-control" value="100" required>
    </div>
    <div class="card">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Admission No</th><th>Student</th><th style="width:150px;">Marks Obtained</th></tr></thead>
            <tbody>
            @forelse($students as $student)
                @php $existing = $student->examResults->first()?->marks_obtained; @endphp
                <tr>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->user->name }}</td>
                    <td><input type="number" step="0.01" min="0" name="marks[{{ $student->id }}]" class="form-control form-control-sm marks-input" value="{{ $existing }}"></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No students in this class.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($students->count())
    <button class="btn btn-dark mt-3">Save Results</button>
    @endif
</form>
<script>
    // Keep each score field capped at the paper's max marks, and refresh the
    // cap live if the teacher changes Max Marks after already typing scores.
    const maxInput = document.getElementById('max_marks');
    function applyMax() {
        document.querySelectorAll('.marks-input').forEach(el => el.max = maxInput.value);
    }
    maxInput.addEventListener('input', applyMax);
    applyMax();
</script>
@else
<p class="text-muted">Select an exam and subject to enter marks.</p>
@endif
@endsection
