@extends('layouts.app')
@section('title', $activity->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.activities.index') }}">Activities</a></li>
        <li class="breadcrumb-item active">{{ $activity->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">
            {{ $activity->name }}
            @if($activity->isHappeningNow())
                <span class="badge bg-success align-middle">Happening now</span>
            @endif
        </h3>
        <div class="text-muted">
            Patron: {{ $activity->patron->user->name ?? 'Not assigned' }}
            @if($activity->venue) &middot; {{ $activity->venue }} @endif
        </div>
        <div class="text-muted small">
            @if($activity->day_of_week)
                {{ $activity->day_of_week }}
                @if($activity->start_time && $activity->end_time)
                    , {{ \Carbon\Carbon::parse($activity->start_time)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($activity->end_time)->format('g:i A') }}
                @endif
            @else
                Not scheduled yet
            @endif
        </div>
    </div>
</div>

@if($activity->description)
<div class="card p-3 mb-3">{{ $activity->description }}</div>
@endif

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Sign Up a Student</h6>
            <form method="POST" action="{{ route('admin.activities.students.store', $activity) }}">
                @csrf
                <select name="student_id" class="form-select mb-2" required>
                    <option value="">Select student</option>
                    @foreach($availableStudents as $student)
                        <option value="{{ $student->id }}">{{ $student->admission_no }} &mdash; {{ $student->user->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-dark w-100">Sign Up</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Signed-up Students ({{ $students->count() }})</div>
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Admission No</th><th>Name</th><th>Signed Up</th><th></th></tr></thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->admission_no }}</td>
                        <td>{{ $student->user->name }}</td>
                        <td>{{ $student->pivot->signed_up_at ? \Carbon\Carbon::parse($student->pivot->signed_up_at)->format('d M Y') : '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.activities.students.destroy', [$activity, $student]) }}" method="POST" onsubmit="return confirm('Remove this student from the activity?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No students signed up yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
