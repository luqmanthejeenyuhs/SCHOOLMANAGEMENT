@extends('layouts.app')
@section('title', 'Finance Overview')
@section('content')
<h3 class="mb-3">Finance Overview</h3>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Cash Position</div>
            <div class="fs-4 fw-bold" style="color:var(--brand-green-dark);">KES {{ number_format($stats['cash_position'], 2) }}</div>
            <div class="text-muted small">Cash + Bank + M-Pesa</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Collected</div>
            <div class="fs-4 fw-bold text-success">KES {{ number_format($stats['collected'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Outstanding</div>
            <div class="fs-4 fw-bold text-danger">KES {{ number_format($stats['outstanding'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Unpaid Invoices</div>
            <div class="fs-4 fw-bold">{{ $stats['unpaid_invoices'] }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card p-3">
            <div class="card-header bg-transparent border-0 px-0 pt-0 fw-semibold" style="color:var(--brand-green-dark);">
                <i class="bi bi-pie-chart"></i> Collected vs Outstanding
            </div>
            <canvas id="feeChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-6 d-flex flex-column gap-2 justify-content-center">
        <a href="{{ route('admin.accounting.chart_of_accounts') }}" class="btn btn-outline-dark text-start"><i class="bi bi-diagram-3"></i> Chart of Accounts</a>
        <a href="{{ route('admin.accounting.journal_entries') }}" class="btn btn-outline-dark text-start"><i class="bi bi-journal-text"></i> Journal Entries</a>
        <a href="{{ route('admin.accounting.ledger') }}" class="btn btn-outline-dark text-start"><i class="bi bi-list-columns-reverse"></i> General Ledger</a>
        <a href="{{ route('admin.accounting.trial_balance') }}" class="btn btn-outline-dark text-start"><i class="bi bi-clipboard-check"></i> Trial Balance</a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const brandGreen = '#012622';
    const brandGold = getComputedStyle(document.documentElement).getPropertyValue('--brand-gold').trim() || '#C9972F';

    new Chart(document.getElementById('feeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Collected', 'Outstanding'],
            datasets: [{
                data: [{{ $stats['collected'] }}, {{ $stats['outstanding'] }}],
                backgroundColor: [brandGreen, brandGold],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
        },
    });
</script>
@endpush
