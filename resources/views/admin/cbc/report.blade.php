@extends('layouts.app')
@section('title', 'CBC Learner Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3>CBC Learner Progress Report</h3>
    <button class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card p-4">
    <div class="text-center mb-3">
        <h5 class="mb-0">DESCRIPTIVE ASSESSMENT SHEET</h5>
        <small class="text-muted">Competency Based Curriculum — Summative Assessment Profile</small>
    </div>

    <div class="row mb-3">
        <div class="col-md-4"><strong>Name:</strong> {{ $student->user->name }}</div>
        <div class="col-md-4"><strong>Admission No:</strong> {{ $student->admission_no }}</div>
        <div class="col-md-4"><strong>UPI (NEMIS):</strong> {{ $student->upi_number ?? '—' }}</div>
        <div class="col-md-4 mt-2"><strong>Class:</strong> {{ $student->schoolClass->name ?? '—' }} {{ $student->section?->name }}</div>
        <div class="col-md-4 mt-2"><strong>School Level:</strong> {{ ucfirst($student->school_level) }}</div>
        <div class="col-md-4 mt-2"><strong>Term:</strong> {{ $term }}</div>
        @if($student->pathway)
        <div class="col-md-4 mt-2"><strong>Pathway:</strong> {{ $student->pathway }}</div>
        @endif
    </div>

    <h6 class="border-bottom pb-1">Learning Areas</h6>
    @forelse($records as $learningAreaName => $areaRecords)
        <div class="fw-semibold small mt-2">{{ $learningAreaName }}</div>
        <table class="table table-sm">
            <thead><tr><th>Strand / Sub-strand</th><th style="width:160px;">Performance Level</th><th>Remarks</th></tr></thead>
            <tbody>
            @foreach($areaRecords as $record)
                <tr>
                    <td>{{ $record->subStrand->strand->name }} — {{ $record->subStrand->name }}</td>
                    <td>
                        <span class="badge
                            @if($record->performance_level==='EE') bg-success
                            @elseif($record->performance_level==='ME') bg-primary
                            @elseif($record->performance_level==='AE') bg-warning text-dark
                            @else bg-danger @endif">
                            {{ $record->performance_level }} — {{ $record->levelLabel() }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $record->remarks ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @empty
        <div class="alert alert-light border">No learning-area assessment records for this term yet.</div>
    @endforelse

    <h6 class="border-bottom pb-1 mt-4">School-Based Assessment (SBA) — Grades 4–6</h6>
    @forelse($sbaRecords as $areaName => $tasks)
        @php
            $totalPct = $tasks->avg(fn($t) => $t->percentage());
            $weighted = round(($totalPct ?? 0) * 0.6, 1);
        @endphp
        <div class="fw-semibold small mt-2">{{ $areaName }} — average {{ round($totalPct ?? 0, 1) }}% (contributes ~{{ $weighted }} of 60 pts toward KPSEA exit profile)</div>
        <table class="table table-sm">
            <thead><tr><th>Task</th><th>Score</th><th>%</th></tr></thead>
            <tbody>
            @foreach($tasks as $task)
                <tr><td>SBA {{ $task->sba_number }}</td><td>{{ $task->score }}/{{ $task->max_score }}</td><td>{{ $task->percentage() }}%</td></tr>
            @endforeach
            </tbody>
        </table>
    @empty
        <div class="alert alert-light border">No SBA scores for this term yet.</div>
    @endforelse

    <h6 class="border-bottom pb-1 mt-4">Core Competencies</h6>
    <table class="table table-sm">
        <thead><tr><th>Competency</th><th style="width:160px;">Level</th><th>Remarks</th></tr></thead>
        <tbody>
        @foreach(\App\Models\CbcCoreCompetencyRecord::COMPETENCIES as $key => $label)
            @php $rec = $coreCompetencies->get($key); @endphp
            <tr>
                <td>{{ $label }}</td>
                <td>
                    @if($rec)
                        <span class="badge
                            @if($rec->performance_level==='EE') bg-success
                            @elseif($rec->performance_level==='ME') bg-primary
                            @elseif($rec->performance_level==='AE') bg-warning text-dark
                            @else bg-danger @endif">{{ $rec->performance_level }}</span>
                    @else
                        <span class="text-muted">Not yet rated</span>
                    @endif
                </td>
                <td class="small text-muted">{{ $rec->remarks ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h6 class="border-bottom pb-1 mt-4">Values &amp; Behaviour</h6>
    <table class="table table-sm">
        <thead><tr><th>Value</th><th style="width:160px;">Rating</th><th>Remarks</th></tr></thead>
        <tbody>
        @foreach(\App\Models\CbcValueRecord::VALUES as $key => $label)
            @php $rec = $values->get($key); @endphp
            <tr>
                <td>{{ $label }}</td>
                <td>
                    @if($rec)
                        <span class="badge
                            @if($rec->rating==='EE') bg-success
                            @elseif($rec->rating==='ME') bg-primary
                            @elseif($rec->rating==='AE') bg-warning text-dark
                            @else bg-danger @endif">{{ $rec->rating }}</span>
                    @else
                        <span class="text-muted">Not yet rated</span>
                    @endif
                </td>
                <td class="small text-muted">{{ $rec->remarks ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h6 class="border-bottom pb-1 mt-4 no-print">Portfolio Evidence</h6>
    <ul class="small text-muted no-print">
        @forelse($portfolioItems as $item)
            <li>{{ $item->title }} ({{ $item->typeLabel() }}) — <a href="{{ route('admin.cbc.portfolio.download', $item) }}">download</a></li>
        @empty
            <li>No portfolio evidence uploaded for this term.</li>
        @endforelse
    </ul>

    <div class="mt-4 small text-muted">
        <strong>Rating Key:</strong>
        EE = Exceeding Expectation &nbsp;|&nbsp;
        ME = Meeting Expectation &nbsp;|&nbsp;
        AE = Approaching Expectation &nbsp;|&nbsp;
        BE = Below Expectation
    </div>
</div>

<style>
@media print {
    .navbar, .sidebar, .no-print { display: none !important; }
}
</style>
@endsection
