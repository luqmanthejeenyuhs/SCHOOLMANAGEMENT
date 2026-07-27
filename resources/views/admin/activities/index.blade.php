@extends('layouts.app')
@section('title', 'Activities')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Classes &amp; Activities</h3>
    <div class="d-flex gap-2 flex-wrap">
        <form method="GET" action="{{ route('admin.activities.index') }}" class="d-flex" style="min-width:260px;">
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search activities or patrons...">
            <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal"><i class="bi bi-plus-lg"></i> Add Activity</button>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.classes.index') }}"><i class="bi bi-building"></i> Classes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.activities.index') }}"><i class="bi bi-trophy"></i> Activities</a>
    </li>
</ul>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Activity</th><th>Patron</th><th>Schedule</th><th>Participants</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($activities as $activity)
            <tr>
                <td><a href="{{ route('admin.activities.show', $activity) }}" class="fw-semibold text-decoration-none">{{ $activity->name }}</a></td>
                <td>{{ $activity->patron->user->name ?? '—' }}</td>
                <td>
                    @if($activity->day_of_week)
                        {{ $activity->day_of_week }}
                        @if($activity->start_time && $activity->end_time)
                            <br><span class="text-muted small">{{ \Carbon\Carbon::parse($activity->start_time)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($activity->end_time)->format('g:i A') }}</span>
                        @endif
                    @else
                        <span class="text-muted">Not scheduled</span>
                    @endif
                </td>
                <td>{{ $activity->students_count }}</td>
                <td>
                    @if($activity->isHappeningNow())
                        <span class="badge bg-success">Happening now</span>
                    @else
                        <span class="badge bg-secondary">Not now</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.activities.show', $activity) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this activity?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">{{ $search ? 'No activities match your search.' : 'No activities yet. Click "Add Activity" above.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Add Activity modal --}}
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.activities.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">e.g. Swimming, Debate Club, Football.</p>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Activity name, e.g. Swimming" required>
                    </div>
                    <div class="mb-2">
                        <select name="patron_id" class="form-select">
                            <option value="">Patron / in charge (optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="day_of_week" class="form-select">
                            <option value="">Day (optional)</option>
                            <option>Daily</option>
                            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <option>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Start</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">End</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="venue" class="form-control" placeholder="Venue, e.g. School Pool">
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control" rows="2" placeholder="Notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
