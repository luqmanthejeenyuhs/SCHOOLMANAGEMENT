@extends('layouts.app')
@section('title', 'CBC Assessment')
@section('content')
<h3 class="mb-3">Record CBC Competency Levels</h3>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('teacher.cbc.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Class</label>
            <select name="school_class_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Learning Area</label>
            <select name="cbc_learning_area_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select learning area</option>
                @foreach($learningAreas as $la)
                    <option value="{{ $la->id }}" @selected($learningAreaId == $la->id)>{{ $la->name }} ({{ ucfirst($la->school_level) }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Term</label>
            <input type="text" name="term" class="form-control" value="{{ $term }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($classId && $learningAreaId && $subStrands->count())
<form method="POST" action="{{ route('teacher.cbc.store') }}">
    @csrf
    <input type="hidden" name="term" value="{{ $term }}">
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        @foreach($subStrands as $sub)
                            <th class="small">{{ $sub->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->user->name }}</td>
                        @foreach($subStrands as $sub)
                            @php $existing = $student->cbcRecords->firstWhere('cbc_sub_strand_id', $sub->id)?->performance_level; @endphp
                            <td>
                                <select name="levels[{{ $student->id }}][{{ $sub->id }}]" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    <option value="EE" @selected($existing==='EE')>EE</option>
                                    <option value="ME" @selected($existing==='ME')>ME</option>
                                    <option value="AE" @selected($existing==='AE')>AE</option>
                                    <option value="BE" @selected($existing==='BE')>BE</option>
                                </select>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-3">No students in this class.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="small text-muted mt-2">EE = Exceeding Expectation · ME = Meeting Expectation · AE = Approaching Expectation · BE = Below Expectation</div>
    @if($students->count())
    <button class="btn btn-dark mt-3">Save Ratings</button>
    @endif
</form>
@else
<p class="text-muted">Select a class and learning area (with sub-strands defined) to begin rating learners.</p>
@endif
@endsection
