@extends('layouts.app')
@section('title', $class->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
        <li class="breadcrumb-item active">{{ $class->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">{{ $class->name }}</h3>
        <span class="text-muted">{{ $class->students_count }} student{{ $class->students_count == 1 ? '' : 's' }} across {{ $sections->count() }} stream{{ $sections->count() == 1 ? '' : 's' }}</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Add Stream</h6>
            <p class="text-muted small">e.g. {{ $class->name }} East, {{ $class->name }} West — add one card per stream so you can drill into each roster separately.</p>
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf
                <input type="hidden" name="school_class_id" value="{{ $class->id }}">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Stream name, e.g. East" required>
                    <button class="btn btn-dark">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3">
            @forelse($sections as $section)
                <div class="col-md-6">
                    <a href="{{ route('admin.sections.show', $section) }}" class="text-decoration-none text-reset">
                        <div class="card p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $class->name }} — {{ $section->name }}</h6>
                                    <span class="text-muted small">{{ $section->students_count }} student{{ $section->students_count == 1 ? '' : 's' }}</span>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-4 text-center text-muted">
                        No streams yet for {{ $class->name }}. Add one on the left — useful when a grade has multiple parallel classes (e.g. Grade 10 East, West, North).
                    </div>
                </div>
            @endforelse

            @if($unassignedCount > 0)
                <div class="col-12">
                    <div class="card p-3 border-warning-subtle bg-warning-subtle">
                        <span>{{ $unassignedCount }} student{{ $unassignedCount == 1 ? ' is' : 's are' }} in {{ $class->name }} but not yet assigned to a stream.</span>
                        <a href="{{ route('admin.students.index') }}" class="small">Assign them from the students list &rarr;</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
