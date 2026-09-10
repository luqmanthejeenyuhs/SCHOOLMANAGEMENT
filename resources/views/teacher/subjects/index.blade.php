@extends('layouts.app')
@section('title', 'My Subjects')
@section('content')

<h3 class="mb-3"><i class="bi bi-journal-text"></i> My Subjects</h3>

@if($subjects->isEmpty())
    <div class="card p-4 text-center text-muted">You're not assigned to teach any subjects yet — ask an admin to attach you to a class and subject.</div>
@else
<div class="row g-3">
    @foreach($subjects as $row)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('teacher.subjects.show', $row->subject) }}" class="text-decoration-none text-dark">
                <div class="card p-3 h-100 stat-card">
                    <h5 class="mb-1">{{ $row->subject->name }}</h5>
                    <div class="text-muted small mb-2">{{ $row->classes->implode(', ') }}</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">{{ $row->student_count }} pupils</span>
                        @if($row->average !== null)
                            <span class="fs-5 fw-bold {{ $row->average >= 50 ? 'text-success' : 'text-danger' }}">{{ $row->average }}%</span>
                        @else
                            <span class="small text-muted">No results yet</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endif

@endsection
