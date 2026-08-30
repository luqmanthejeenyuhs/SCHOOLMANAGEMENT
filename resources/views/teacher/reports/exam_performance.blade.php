@extends('layouts.app')
@section('title', 'Exam Performance')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Exam Performance</h3>
    <a href="{{ route('teacher.reports.index') }}" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
</div>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('teacher.reports.exam-performance') }}" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small">Exam</label>
            <select name="exam_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select exam</option>
                @foreach($exams as $e)
                    <option value="{{ $e->id }}" @selected($examId == $e->id)>{{ $e->name }} — {{ $e->schoolClass->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

@if($exam)
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Rank</th>
                    <th>Student</th>
                    @foreach($subjectColumns as $subject)
                        <th>{{ $subject->name }}</th>
                    @endforeach
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->rank ?? '—' }}</td>
                    <td>{{ $row->student->user->name }}</td>
                    @foreach($subjectColumns as $subject)
                        <td>{{ $row->by_subject[$subject->id] !== null ? $row->by_subject[$subject->id].'%' : '—' }}</td>
                    @endforeach
                    <td class="fw-semibold">{{ $row->average !== null ? $row->average.'%' : 'No marks yet' }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $subjectColumns->count() + 3 }}" class="text-center text-muted py-4">No students in this class.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">Average is calculated across whatever subjects have been recorded for this exam so far — it will fill in as more subject teachers enter their marks.</p>
@else
<p class="text-muted">Select an exam to see performance and ranking.</p>
@endif
@endsection
