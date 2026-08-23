@extends('layouts.app')
@section('title', 'School Profile')
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.settings.index') }}" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Settings</a>
    <h3 class="mb-0 mt-1">School Profile</h3>
</div>

<div class="card p-4" style="max-width:700px;">
    <form method="POST" action="{{ route('admin.settings.school_profile.update') }}">
        @csrf @method('PUT')

        <h6 class="text-uppercase text-muted small mb-3">Basic Details</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">School Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $school->address) }}">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Staff Attendance Policy</h6>
        <p class="text-muted small mt-n2">Used by Clock In/Out — anyone clocking in after "Expected Clock In" (plus a 10-minute grace period) is automatically marked Late.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Expected Clock In</label>
                <input type="time" name="expected_clock_in" class="form-control" value="{{ old('expected_clock_in', $school->expected_clock_in ? \Carbon\Carbon::parse($school->expected_clock_in)->format('H:i') : '08:00') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Expected Clock Out</label>
                <input type="time" name="expected_clock_out" class="form-control" value="{{ old('expected_clock_out', $school->expected_clock_out ? \Carbon\Carbon::parse($school->expected_clock_out)->format('H:i') : '16:00') }}" required>
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Compound Location (Geofence)</h6>
        <p class="text-muted small mt-n2">Staff clocking in from within this radius are marked "on-compound" automatically. Outside it (or with location off), they're still clocked in, just flagged for review.</p>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude" id="lat-input" class="form-control" value="{{ old('latitude', $school->latitude) }}" placeholder="e.g. -1.286389">
            </div>
            <div class="col-md-5">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude" id="lng-input" class="form-control" value="{{ old('longitude', $school->longitude) }}" placeholder="e.g. 36.817223">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="use-location-btn" class="btn btn-outline-secondary w-100">📍 Use my location</button>
            </div>
            <div class="col-md-6">
                <label class="form-label">Geofence Radius (metres)</label>
                <input type="number" name="geofence_radius_meters" class="form-control" value="{{ old('geofence_radius_meters', $school->geofence_radius_meters ?? 200) }}" min="20" max="5000">
                <div class="form-text">200m covers most single-compound schools. Increase it if staff are clocking in from a spread-out campus.</div>
            </div>
        </div>
        <div id="location-status" class="small text-muted mt-2"></div>

        <div class="mt-4">
            <button class="btn btn-dark">Save</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('use-location-btn').addEventListener('click', function () {
    const status = document.getElementById('location-status');
    if (!navigator.geolocation) {
        status.textContent = 'Location not supported by this browser — enter coordinates manually.';
        return;
    }
    status.textContent = 'Getting your location…';
    navigator.geolocation.getCurrentPosition(
        function (pos) {
            document.getElementById('lat-input').value = pos.coords.latitude;
            document.getElementById('lng-input').value = pos.coords.longitude;
            status.textContent = 'Location captured. Stand at the school compound before clicking this for the most accurate result, then Save.';
            status.classList.add('text-success');
        },
        function () {
            status.textContent = 'Could not get your location — permission denied or unavailable. Enter coordinates manually instead.';
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
});
</script>
@endpush
@endsection
