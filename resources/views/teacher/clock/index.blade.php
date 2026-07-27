@extends('layouts.app')
@section('title', 'Clock In/Out')
@section('content')
<h3 class="mb-3">Clock In / Out</h3>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card p-4" style="max-width:420px;">
    @if(!$employee)
        <p class="text-muted mb-0">No staff record is linked to your account yet. Ask an admin to link your profile before you can clock in.</p>
    @else
        <p class="mb-1"><strong>Today:</strong> {{ today()->format('l, d M Y') }}</p>
        <p class="mb-1">Clock In: {{ $today && $today->clock_in ? \Carbon\Carbon::parse($today->clock_in)->format('g:i A') : '— not yet —' }}</p>
        <p class="mb-3">Clock Out: {{ $today && $today->clock_out ? \Carbon\Carbon::parse($today->clock_out)->format('g:i A') : '— not yet —' }}</p>

        <form method="POST" action="{{ route('teacher.clock.store') }}">
            @csrf
            @if(!$today)
                <button class="btn btn-dark w-100">Clock In</button>
            @elseif(!$today->clock_out)
                <button class="btn btn-dark w-100">Clock Out</button>
            @else
                <button class="btn btn-secondary w-100" disabled>Already clocked out for today</button>
            @endif
        </form>
    @endif
</div>
@endsection
