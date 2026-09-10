@extends('layouts.app')
@section('title', 'My Timetable')
@section('content')

<h3 class="mb-3"><i class="bi bi-calendar-week"></i> My Timetable</h3>

@if($slots->isEmpty())
    <div class="card p-4 text-center text-muted">Nothing on your timetable yet — ask an admin to add your lessons under Classes → Timetable.</div>
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
                    @php
                        [$start, $end] = explode('|', $range);
                        $daySlots = collect($days)->mapWithKeys(fn($day) => [$day => $slots->first(fn($s) => $s->day_of_week === $day && $s->start_time == $start && $s->end_time == $end)]);
                        // A break/lunch that applies at this time on every
                        // single weekday shows as one spanning row, matching
                        // how a printed school timetable normally looks,
                        // instead of repeating the same label five times.
                        $schoolWideEveryDay = $daySlots->every(fn($s) => $s && in_array($s->slot_type, \App\Models\TimetableSlot::SCHOOL_WIDE_TYPES))
                            && $daySlots->pluck('slot_type')->unique()->count() === 1;
                    @endphp
                    <tr>
                        <td class="fw-semibold small">{{ \Carbon\Carbon::parse($start)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($end)->format('g:i A') }}</td>
                        @if($schoolWideEveryDay)
                            <td colspan="{{ count($days) }}" class="bg-warning-subtle fw-semibold small">
                                {{ $daySlots->first()->displayLabel() }}
                            </td>
                        @else
                            @foreach($days as $day)
                                @php $slot = $daySlots[$day]; @endphp
                                <td class="{{ $slot ? 'bg-light' : '' }}">
                                    @if($slot)
                                        <div class="fw-semibold small">{{ $slot->displayLabel() }}</div>
                                        @if($slot->room)<div class="text-muted small">{{ $slot->room }}</div>@endif
                                    @else
                                        <span class="text-muted small">Free Period</span>
                                    @endif
                                </td>
                            @endforeach
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
