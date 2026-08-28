@extends('layouts.app')
@section('title', 'My Activities')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-stars"></i> Extra-Curricular Activities</h3>
</div>

<div class="card p-3 mb-3">
    <h6>I'm signed up for</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Activity</th><th>Patron</th><th>Schedule</th><th>Venue</th></tr></thead>
        <tbody>
        @forelse($myActivities as $a)
            <tr>
                <td>{{ $a->name }} @if($a->isHappeningNow()) <span class="badge bg-success">Happening now</span> @endif</td>
                <td>{{ $a->patron->user->name ?? '—' }}</td>
                <td>{{ $a->day_of_week ?? '—' }} @if($a->start_time) {{ \Illuminate\Support\Carbon::parse($a->start_time)->format('g:i A') }}–{{ \Illuminate\Support\Carbon::parse($a->end_time)->format('g:i A') }} @endif</td>
                <td>{{ $a->venue ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">You're not signed up for any activities yet — ask your class teacher or the activity patron to add you.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card p-3">
    <h6>Other activities at school</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Activity</th><th>Patron</th><th>Schedule</th><th>Venue</th></tr></thead>
        <tbody>
        @forelse($otherActivities as $a)
            <tr>
                <td>{{ $a->name }}</td>
                <td>{{ $a->patron->user->name ?? '—' }}</td>
                <td>{{ $a->day_of_week ?? '—' }} @if($a->start_time) {{ \Illuminate\Support\Carbon::parse($a->start_time)->format('g:i A') }}–{{ \Illuminate\Support\Carbon::parse($a->end_time)->format('g:i A') }} @endif</td>
                <td>{{ $a->venue ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No other activities are running at the moment.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
