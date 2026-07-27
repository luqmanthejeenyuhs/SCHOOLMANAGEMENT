@extends('layouts.app')
@section('title', $parent->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">Parents</a></li>
        <li class="breadcrumb-item active">{{ $parent->name }}</li>
    </ol>
</nav>

<div class="mb-3">
    <h3 class="mb-0">{{ $parent->name }}</h3>
    <span class="text-muted">{{ $parent->email }} @if($parent->phone) &middot; {{ $parent->phone }} @endif</span>
</div>

<div class="card">
    <div class="card-header">Linked Children</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Admission No</th><th>Name</th><th>Class</th><th>Stream</th><th>Relationship</th></tr>
            </thead>
            <tbody>
            @forelse($parent->children as $child)
                <tr>
                    <td>{{ $child->admission_no }}</td>
                    <td><a href="{{ route('admin.students.show', $child) }}">{{ $child->user->name }}</a></td>
                    <td>{{ $child->schoolClass->name ?? '—' }}</td>
                    <td>{{ $child->section->name ?? '—' }}</td>
                    <td>{{ $child->pivot->relationship ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No children linked yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
