@extends('layouts.app')
@section('title', 'My Results')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-clipboard-data"></i> My Results</h3>
    <small class="text-muted">{{ $student->schoolClass->name ?? '—' }} @if($student->section) · {{ $student->section->name }} @endif · Admission No: {{ $student->admission_no }}</small>
</div>

@forelse($examResults as $examName => $rows)
    <div class="card p-3 mb-3">
        <h6 class="mb-3">{{ $examName }}</h6>
        <table class="table table-sm mb-0">
            <thead><tr><th>Subject</th><th>Marks</th><th>%</th><th>Grade</th></tr></thead>
            <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->subject->name ?? '—' }}</td>
                    <td>{{ $r->marks_obtained }}/{{ $r->max_marks }}</td>
                    <td>{{ $r->percentage() }}%</td>
                    <td><span class="badge bg-info text-dark">{{ $r->grade ?? '—' }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="card p-4 text-center text-muted">No results have been published yet.</div>
@endforelse

@endsection
