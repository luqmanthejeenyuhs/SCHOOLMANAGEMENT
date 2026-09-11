@extends('layouts.app')
@section('title', 'Platform Dashboard')
@section('content')

<h3 class="mb-3"><i class="bi bi-globe"></i> Platform Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Total Schools</span>
            <h4 class="mb-0">{{ $stats['total_schools'] }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Active / Suspended</span>
            <h4 class="mb-0"><span class="text-success">{{ $stats['active_schools'] }}</span> / <span class="text-danger">{{ $stats['suspended_schools'] }}</span></h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Total Students</span>
            <h4 class="mb-0">{{ number_format($stats['total_students']) }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Total Teachers</span>
            <h4 class="mb-0">{{ number_format($stats['total_teachers']) }}</h4>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Platform Billing Outstanding</span>
            <h4 class="mb-0 text-danger">KES {{ number_format($billing['outstanding'], 2) }}</h4>
            <small class="text-muted">{{ $billing['overdue_count'] }} overdue</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Collected This Month</span>
            <h4 class="mb-0 text-success">KES {{ number_format($billing['collected_this_month'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 text-center">
            <span class="text-muted small">Fee Payments Processed (all schools)</span>
            <h4 class="mb-0">KES {{ number_format($stats['fee_payments_processed'], 2) }}</h4>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-3 mb-4">
            <h6 class="mb-3">Schools by Plan</h6>
            @foreach(['trial', 'basic', 'premium'] as $plan)
                @php $count = $schoolsByPlan[$plan] ?? 0; @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-capitalize">{{ $plan }}</span>
                    <span class="fw-semibold">{{ $count }}</span>
                </div>
                <div class="progress mb-3" style="height:8px;">
                    <div class="progress-bar" style="width: {{ $stats['total_schools'] ? ($count / $stats['total_schools'] * 100) : 0 }}%; background: var(--brand-green);"></div>
                </div>
            @endforeach
        </div>

        @if($trialsExpiringSoon->isNotEmpty())
        <div class="card p-3 mb-4 border-warning">
            <h6 class="mb-3 text-warning"><i class="bi bi-exclamation-triangle"></i> Trials Expiring Within 7 Days</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>School</th><th>Trial Ends</th></tr></thead>
                <tbody>
                @foreach($trialsExpiringSoon as $school)
                    <tr>
                        <td><a href="{{ route('superadmin.schools.edit', $school) }}">{{ $school->name }}</a></td>
                        <td>{{ $school->trial_ends_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="card p-3">
            <h6 class="mb-3">Top Schools by Students</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>School</th><th>Students</th></tr></thead>
                <tbody>
                @foreach($topSchoolsByStudents as $school)
                    <tr>
                        <td>{{ $school->name }}</td>
                        <td>{{ $school->students_count }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-3 mb-4">
            <h6 class="mb-3">Signups — Last 6 Months</h6>
            <table class="table table-sm mb-0">
                <tbody>
                @foreach($signupTrend as $row)
                    <tr>
                        <td style="width:100px;">{{ $row['label'] }}</td>
                        <td>
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar" style="width: {{ min($row['count'] * 20, 100) }}%; background: var(--brand-gold-dark);"></div>
                            </div>
                        </td>
                        <td style="width:30px;" class="text-end fw-semibold">{{ $row['count'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Recent Platform-Wide Activity</h6>
                <a href="{{ route('superadmin.audit_logs.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <ul class="list-unstyled mb-0">
                @forelse($recentActivity as $log)
                    <li class="py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="small">
                            <span class="badge {{ str_contains($log->event, 'failed') ? 'bg-danger' : 'bg-success' }}">{{ ucwords(str_replace('_', ' ', $log->event)) }}</span>
                            {{ $log->user?->name ?? $log->username_attempted ?? 'Unknown' }}
                            @if($log->school) <span class="text-muted">— {{ $log->school->name }}</span> @endif
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $log->created_at?->diffForHumans() }} · {{ $log->ip_address }}</div>
                    </li>
                @empty
                    <li class="text-muted small text-center py-3">No activity recorded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@endsection
