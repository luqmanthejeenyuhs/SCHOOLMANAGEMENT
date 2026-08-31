@extends('layouts.app')
@section('title', 'Platform Dashboard')
@section('content')
<h3 class="mb-4">Platform Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card h-100 p-3">
            <div class="text-muted small text-uppercase">Schools</div>
            <div class="fs-3 fw-bold">{{ $totalSchools }}</div>
            <div class="small text-muted">
                <span class="text-success">{{ $activeSchools }} active</span>
                @if($suspendedSchools > 0)
                    &middot; <span class="text-danger">{{ $suspendedSchools }} suspended</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100 p-3">
            <div class="text-muted small text-uppercase">Students</div>
            <div class="fs-3 fw-bold">{{ number_format($totalStudents) }}</div>
            <div class="small text-muted">across all schools</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100 p-3">
            <div class="text-muted small text-uppercase">Staff</div>
            <div class="fs-3 fw-bold">{{ number_format($totalStaff) }}</div>
            <div class="small text-muted">{{ number_format($totalTeachers) }} teaching</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100 p-3" style="border-left: 4px solid var(--brand-gold);">
            <div class="text-muted small text-uppercase">Est. Monthly Revenue</div>
            <div class="fs-3 fw-bold">KES {{ number_format($estimatedMonthlyRevenue) }}</div>
            <div class="small text-muted">@ KES {{ $ratePerStudent }}/student &middot; active schools only</div>
        </div>
    </div>
</div>

@if($trialsExpiringSoon->isNotEmpty())
<div class="card p-3 mb-4" style="border-left: 4px solid #dc3545;">
    <h6 class="mb-3"><i class="bi bi-exclamation-triangle text-danger"></i> Trials Expiring Within 7 Days</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>School</th><th>Trial Ends</th><th></th></tr></thead>
        <tbody>
        @foreach($trialsExpiringSoon as $school)
            <tr>
                <td>{{ $school->name }}</td>
                <td>{{ $school->trial_ends_at->format('d M Y') }} <span class="text-muted small">({{ $school->trial_ends_at->diffForHumans() }})</span></td>
                <td class="text-end"><a href="{{ route('superadmin.schools.edit', $school) }}" class="btn btn-sm btn-outline-dark">Manage</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="mb-3">New Schools — Last 6 Months</h6>
            <div class="d-flex align-items-end gap-2" style="height:140px;">
                @php $max = max($growth->max('count'), 1); @endphp
                @foreach($growth as $point)
                    <div class="flex-fill text-center">
                        <div class="mx-auto" style="width:70%; height:{{ $point['count'] > 0 ? max(($point['count'] / $max) * 110, 6) : 2 }}px; background: var(--brand-green); border-radius: 4px 4px 0 0;" title="{{ $point['count'] }} schools"></div>
                        <div class="small text-muted mt-1">{{ $point['count'] }}</div>
                        <div class="small text-muted">{{ $point['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="text-muted small mt-2">{{ $newSchoolsThisMonth }} new this month</div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('superadmin.schools.create') }}" class="btn btn-dark"><i class="bi bi-plus-circle"></i> Onboard a New School</a>
                <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-dark"><i class="bi bi-buildings"></i> Manage All Schools</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Per-School Breakdown</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>School</th>
                    <th>Status</th>
                    <th class="text-end">Students</th>
                    <th class="text-end">Est. Monthly Bill</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($schools as $school)
                <tr>
                    <td>{{ $school->name }}</td>
                    <td>
                        @if($school->is_active)
                            <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis">Suspended</span>
                        @endif
                    </td>
                    <td class="text-end font-monospace">{{ number_format($school->students_count) }}</td>
                    <td class="text-end font-monospace">{{ $school->is_active ? 'KES '.number_format($school->estimated_monthly_bill) : '—' }}</td>
                    <td class="text-end"><a href="{{ route('superadmin.schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No schools yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
