@extends('layouts.app')
@section('title', 'Record SBA Scores')
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.cbc.index') }}">CBC Curriculum</a></li>
        <li class="breadcrumb-item active">Record SBA Scores</li>
    </ol>
</nav>
<h3 class="mb-3">Record School-Based Assessment (SBA) Scores</h3>
<p class="text-muted small">Each SBA performance task contributes 20% (60% total across SBA 1–3) to the KPSEA exit profile for Grades 4–6.</p>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('admin.cbc.sba.grid') }}" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Class</label>
            <select name="school_class_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Stream</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                <option value="">All streams</option>
                @foreach($classes as $class)
                    @if($classId == $class->id)
                        @foreach($class->sections as $section)
                            <option value="{{ $section->id }}" @selected($sectionId == $section->id)>{{ $section->name }}</option>
                        @endforeach
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Learning Area</label>
            <select name="cbc_learning_area_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select learning area</option>
                @foreach($learningAreas as $la)
                    <option value="{{ $la->id }}" @selected($learningAreaId == $la->id)>{{ $la->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">SBA Task</label>
            <select name="sba_number" class="form-select" onchange="this.form.submit()">
                <option value="1" @selected($sbaNumber == 1)>SBA 1</option>
                <option value="2" @selected($sbaNumber == 2)>SBA 2</option>
                <option value="3" @selected($sbaNumber == 3)>SBA 3</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Term</label>
            <input type="text" name="term" class="form-control" value="{{ $term }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($classId && $learningAreaId)
<form method="POST" action="{{ route('admin.cbc.sba.store') }}">
    @csrf
    <input type="hidden" name="cbc_learning_area_id" value="{{ $learningAreaId }}">
    <input type="hidden" name="sba_number" value="{{ $sbaNumber }}">
    <input type="hidden" name="term" value="{{ $term }}">
    <div class="card p-3 mb-3" style="max-width:200px;">
        <label class="form-label small">Max Score</label>
        <input type="number" name="max_score" id="max_score" class="form-control" value="100" required>
    </div>
    <div class="card">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Admission No</th><th>Student</th><th style="width:150px;">Score</th></tr></thead>
            <tbody>
            @forelse($students as $student)
                @php $existing = $student->sbaRecords->first()?->score; @endphp
                <tr>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->user->name }}</td>
                    <td><input type="number" step="0.01" min="0" name="scores[{{ $student->id }}]" class="form-control form-control-sm sba-input" value="{{ $existing }}"></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No students in this class.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($students->count())
    <button class="btn btn-dark mt-3">Save Scores</button>
    @endif
</form>
<script>
    const maxInput = document.getElementById('max_score');
    function applyMax() { document.querySelectorAll('.sba-input').forEach(el => el.max = maxInput.value); }
    maxInput.addEventListener('input', applyMax);
    applyMax();
</script>
@else
<p class="text-muted">Select a class and a learning area to begin.</p>
@endif
@endsection
