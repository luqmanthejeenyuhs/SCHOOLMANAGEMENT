@extends('layouts.app')
@section('title', 'Clock In/Out')
@section('content')
<h3 class="mb-3">Clock In / Out</h3>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card p-4" style="max-width:460px;">
    @if(!$employee)
        <p class="text-muted mb-0">No staff record is linked to your account yet. Ask an admin to link your profile before you can clock in.</p>
    @else
        <p class="mb-1"><strong>Today:</strong> {{ today()->format('l, d M Y') }}</p>
        <p class="mb-1">
            Clock In:
            {{ $today && $today->clock_in ? $today->clock_in->format('g:i A') : '— not yet —' }}
            @if($today && $today->status === 'late')
                <span class="badge bg-warning text-dark ms-1">Late</span>
            @endif
        </p>
        <p class="mb-3">Clock Out: {{ $today && $today->clock_out ? $today->clock_out->format('g:i A') : '— not yet —' }}</p>

        @if($today && $today->remarks)
            <p class="text-muted small mb-3">{{ $today->remarks }}</p>
        @endif

        <form method="POST" action="{{ route('teacher.clock.store') }}" id="clock-form">
            @csrf
            <input type="hidden" name="action" id="clock-action" value="{{ !$today ? 'clock_in' : 'clock_out' }}">
            <input type="hidden" name="lat" id="clock-lat">
            <input type="hidden" name="lng" id="clock-lng">

            <div id="clock-status" class="small text-muted mb-2">Checking your location…</div>

            @if(!$today)
                <button class="btn btn-dark w-100" id="clock-btn">Clock In</button>
            @elseif(!$today->clock_out)
                <button class="btn btn-dark w-100" id="clock-btn">Clock Out</button>
            @else
                <button class="btn btn-secondary w-100" disabled>Already clocked out for today</button>
            @endif
        </form>

        <p class="text-muted small mt-3 mb-0">
            If location is on and you're within {{ $school?->effectiveGeofenceRadiusMeters() ?? 200 }}m of the school, this is recorded as a verified on-compound clock-in. Otherwise it's still recorded, just flagged for admin review.
        </p>
    @endif
</div>

@if($employee)
@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('clock-status');
    const latEl = document.getElementById('clock-lat');
    const lngEl = document.getElementById('clock-lng');
    const btn = document.getElementById('clock-btn');

    if (!navigator.geolocation) {
        statusEl.textContent = 'Location not supported on this device — you can still clock in, it\'ll just be unverified.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            latEl.value = pos.coords.latitude;
            lngEl.value = pos.coords.longitude;
            statusEl.textContent = 'Location captured.';
            statusEl.classList.add('text-success');
        },
        function () {
            statusEl.textContent = 'Location unavailable or permission denied — you can still clock in, it\'ll just be unverified.';
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
})();
</script>
@endpush
@endif
@endsection
