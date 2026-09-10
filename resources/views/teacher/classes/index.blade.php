@extends('layouts.app')
@section('title', 'My Classes & Subjects')
@section('content')
<h3 class="mb-3">My Classes &amp; Subjects</h3>

<div class="row g-3">
    @forelse($sections as $section)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('teacher.classes.show', $section) }}" class="text-decoration-none text-dark">
                <div class="card h-100 p-3 {{ $section->is_class_teacher ? 'border-warning border-2' : '' }}">
                    @if($section->is_class_teacher)
                        <span class="badge bg-warning text-dark mb-2 align-self-start"><i class="bi bi-star-fill"></i> Class Teacher</span>
                    @endif
                    <div class="fw-semibold mb-1"><i class="bi bi-door-open"></i> {{ $section->schoolClass->name }} — {{ $section->name }}</div>
                    <div class="text-muted small mb-2">{{ $section->pupil_count }} pupil(s)</div>

                    @if($section->subjects_taught->isNotEmpty())
                        <div class="mt-auto">
                            <div class="text-muted small mb-1">You teach:</div>
                            @foreach($section->subjects_taught as $subject)
                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $subject->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </a>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-3">No classes assigned yet — ask the admin to attach you as a class teacher or subject teacher.</div>
    @endforelse
</div>
@endsection
