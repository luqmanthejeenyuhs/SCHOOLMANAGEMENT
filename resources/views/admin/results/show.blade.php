@extends('layouts.app')
@section('title', 'Exam Results')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">{{ $exam->name }} — {{ $exam->schoolClass->name }}</h3>
        @if($exam->term)<span class="text-muted">{{ $exam->term }}</span>@endif
    </div>
    <div>
        <a href="{{ route('admin.exams.report_cards.pdf', $exam) }}" class="btn btn-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Download All Report Cards (PDF)</a>
        <a href="{{ route('admin.grading_scales.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders"></i> Grading Scale</a>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary btn-sm">Back to Exams</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    @foreach($subjects as $subject)
                        <th>{{ $subject->name }}</th>
                    @endforeach
                    <th>Total</th>
                    <th>Mean %</th>
                    <th>Grade</th>
                    <th>Class Pos.</th>
                    <th>Stream Pos.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['class_position'] }}</td>
                    <td>{{ $row['student']->user->name }}</td>
                    @foreach($subjects as $subject)
                        @php $result = $row['results'][$subject->id] ?? null; @endphp
                        <td>
                            @if($result)
                                {{ $result->marks_obtained }}/{{ $result->max_marks }}
                                <span class="badge bg-secondary">{{ $result->grade ?? '—' }}</span>
                                @if(($row['subject_positions'][$subject->id] ?? null))
                                    <span class="text-muted small">(#{{ $row['subject_positions'][$subject->id] }})</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                    <td class="fw-bold">{{ $row['total'] }}/{{ $row['possible'] }}</td>
                    <td class="fw-bold">{{ $row['mean'] }}%</td>
                    <td><span class="badge bg-dark">{{ $row['grade'] ?? '—' }}</span></td>
                    <td>{{ $row['class_position'] }} / {{ count($rows) }}</td>
                    <td>{{ $row['stream_position'] ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.exams.report_card', [$exam, $row['student']]) }}" class="btn btn-sm btn-outline-primary">Report Card</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ 7 + $subjects->count() }}" class="text-center text-muted py-4">No results entered yet. Teachers can enter marks from their "Enter Results" page.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">Numbers in brackets next to a subject score show that student's position in the class for that subject.</p>
@endsection
