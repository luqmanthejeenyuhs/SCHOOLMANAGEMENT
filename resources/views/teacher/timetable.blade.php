@extends('layouts.app')
@section('title', 'My Timetable')
@section('content')
<h3 class="mb-3">My Timetable</h3>

@if($slots->isEmpty())
    <div class="card p-4 text-center text-muted">No lessons have been scheduled for you yet — check back once the admin has built the class timetables.</div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px;">Time</th>
                        @foreach($days as $day)
                            <th>{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($timeRanges as $range)
                    @php [$start, $end] = explode('|', $range); @endphp
                    <tr>
                        <td class="fw-semibold small">{{ \Carbon\Carbon::parse($start)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($end)->format('g:i A') }}</td>
                        @foreach($days as $day)
                            @php $slot = $slots->first(fn($s) => $s->day_of_week === $day && $s->start_time == $start && $s->end_time == $end); @endphp
                            <td class="{{ $slot ? 'bg-light' : '' }}">
                                @if($slot)
                                    <div class="fw-semibold small">{{ $slot->subject->name }}</div>
                                    <div class="text-muted small">{{ $slot->section->schoolClass->name }} {{ $slot->section->name }}</div>
                                    @if($slot->room)<div class="text-muted small">{{ $slot->room }}</div>@endif
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
