@extends('layouts.app')
@section('title', 'Record Values & Behaviour')
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.cbc.index') }}">CBC Curriculum</a></li>
        <li class="breadcrumb-item active">Record Values &amp; Behaviour</li>
    </ol>
</nav>
<h3 class="mb-3">Record Values &amp; Behaviour</h3>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('admin.cbc.values.grid') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
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
        <div class="col-md-4">
            <label class="form-label small">Value</label>
            <select name="value_area" class="form-select" onchange="this.form.submit()">
                <option value="">Select value</option>
                @foreach(\App\Models\CbcValueRecord::VALUES as $value => $label)
                    <option value="{{ $value }}" @selected($valueArea === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Term</label>
            <input type="text" name="term" class="form-control" value="{{ $term }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($classId && $valueArea)
<form method="POST" action="{{ route('admin.cbc.values.store') }}">
    @csrf
    <input type="hidden" name="value_area" value="{{ $valueArea }}">
    <input type="hidden" name="term" value="{{ $term }}">
    <div class="card">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Admission No</th><th>Student</th><th style="width:220px;">Rating</th></tr></thead>
            <tbody>
            @forelse($students as $student)
                @php $existing = $student->valueRecords->first()?->rating; @endphp
                <tr>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->user->name }}</td>
                    <td>
                        <select name="ratings[{{ $student->id }}]" class="form-select form-select-sm">
                            <option value="">— Not rated —</option>
                            @foreach(\App\Models\CbcValueRecord::RATINGS as $code => $label)
                                <option value="{{ $code }}" @selected($existing === $code)>{{ $code }} — {{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No students in this class.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($students->count())
    <button class="btn btn-dark mt-3">Save Ratings</button>
    @endif
</form>
@else
<p class="text-muted">Select a class and a value to begin.</p>
@endif
@endsection
