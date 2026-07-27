@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="p-4 mb-4 rounded-4 text-white" style="background:linear-gradient(120deg,var(--brand-blue-dark),var(--brand-blue) 60%,var(--brand-blue-mid));">
    <h3 class="mb-1 fw-bold"><i class="bi bi-speedometer2"></i> Admin Dashboard</h3>
    <div class="opacity-75 small">Welcome back — here's what's happening at your school today.</div>
</div>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Students</div>
            <div class="fs-2 fw-bold">{{ $stats['students'] }}</div>
            <i class="bi bi-people"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Teachers</div>
            <div class="fs-2 fw-bold">{{ $stats['teachers'] }}</div>
            <i class="bi bi-person-badge"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Classes</div>
            <div class="fs-2 fw-bold">{{ $stats['classes'] }}</div>
            <i class="bi bi-building"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Present Today</div>
            <div class="fs-2 fw-bold">{{ $stats['today_present'] }}</div>
            <i class="bi bi-calendar-check"></i>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small">Unpaid / Partially Paid Invoices</div>
            <div class="fs-2 fw-bold text-danger">{{ $stats['unpaid_invoices'] }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Fees Collected</div>
            <div class="fs-2 fw-bold" style="color:var(--brand-blue-dark);">KES {{ number_format($stats['collected_this_month'], 2) }}</div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('admin.students.create') }}" class="btn btn-dark me-2"><i class="bi bi-plus-lg"></i> Admit Student</a>
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-outline-dark me-2"><i class="bi bi-plus-lg"></i> Add Teacher</a>
    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-dark"><i class="bi bi-receipt"></i> Manage Fees</a>
</div>
@endsection
