@extends('layouts.app')
@section('title', 'My Performance')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-graph-up-arrow"></i> My Performance</h3>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Overall Average</span>
            <h4 class="mb-0">{{ $overallAverage !== null ? $overallAverage.'%' : '—' }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Subjects Tracked</span>
            <h4 class="mb-0">{{ $bySubject->count() }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Exams Sat</span>
            <h4 class="mb-0">{{ $byExam->count() }}</h4>
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <h6 class="mb-3">Average by Subject</h6>
    @if($bySubject->isEmpty())
        <p class="text-muted text-center py-3 mb-0">No results yet to calculate performance from.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Subject</th><th>Average</th><th>Best</th><th>Worst</th><th>Exams</th><th style="width:220px;"></th></tr></thead>
            <tbody>
            @foreach($bySubject as $subject => $stats)
                <tr>
                    <td>{{ $subject }}</td>
                    <td>{{ $stats['average'] }}%</td>
                    <td class="text-success">{{ $stats['best'] }}%</td>
                    <td class="text-danger">{{ $stats['worst'] }}%</td>
                    <td>{{ $stats['count'] }}</td>
                    <td>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $stats['average'] }}%; background: var(--brand-green);"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card p-3">
    <h6 class="mb-3">Average by Exam (over time)</h6>
    @if($byExam->isEmpty())
        <p class="text-muted text-center py-3 mb-0">No exams recorded yet.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Exam</th><th>Average</th><th style="width:220px;"></th></tr></thead>
            <tbody>
            @foreach($byExam as $exam => $avg)
                <tr>
                    <td>{{ $exam }}</td>
                    <td>{{ $avg }}%</td>
                    <td>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $avg }}%; background: var(--brand-gold-dark);"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
