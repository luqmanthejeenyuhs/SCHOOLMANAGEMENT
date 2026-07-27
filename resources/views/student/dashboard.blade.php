@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')
@if(!$student)
    <div class="alert alert-warning">No student profile linked to your account. Contact the admin.</div>
@else
<h3 class="mb-3">Welcome, {{ auth()->user()->name }}</h3>
<p class="text-muted">{{ $student->schoolClass->name ?? '—' }} @if($student->section) - Section {{ $student->section->name }} @endif · Admission No: {{ $student->admission_no }}</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">Days Present</div>
            <div class="fs-2 fw-bold text-success">{{ $attendanceSummary['present'] }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">Days Absent</div>
            <div class="fs-2 fw-bold text-danger">{{ $attendanceSummary['absent'] }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="text-muted small">Days Late</div>
            <div class="fs-2 fw-bold text-warning">{{ $attendanceSummary['late'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-3">
            <h6>Recent Attendance</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($student->attendances as $a)
                    <tr>
                        <td>{{ $a->date->format('d M Y') }}</td>
                        <td>
                            @if($a->status === 'present') <span class="badge bg-success">Present</span>
                            @elseif($a->status === 'absent') <span class="badge bg-danger">Absent</span>
                            @elseif($a->status === 'late') <span class="badge bg-warning text-dark">Late</span>
                            @else <span class="badge bg-secondary">Excused</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-3">No attendance records yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h6>Exam Results</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead>
                <tbody>
                @forelse($student->examResults as $r)
                    <tr>
                        <td>{{ $r->exam->name }}</td>
                        <td>{{ $r->subject->name }}</td>
                        <td>{{ $r->marks_obtained }}/{{ $r->max_marks }}</td>
                        <td><span class="badge bg-info text-dark">{{ $r->grade }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No results yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card p-3">
            <h6>Fee Invoices</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Fee Type</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($student->feeInvoices as $inv)
                    <tr>
                        <td>{{ $inv->feeType->name }}</td>
                        <td>KES {{ number_format($inv->amount, 2) }}</td>
                        <td>KES {{ number_format($inv->totalPaid(), 2) }}</td>
                        <td>KES {{ number_format($inv->balance(), 2) }}</td>
                        <td>
                            @if($inv->status === 'paid') <span class="badge bg-success">Paid</span>
                            @elseif($inv->status === 'partially_paid') <span class="badge bg-warning text-dark">Partial</span>
                            @else <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
