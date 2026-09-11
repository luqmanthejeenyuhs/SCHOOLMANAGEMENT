@extends('layouts.app')
@section('title', 'New Announcement')
@section('content')

<h3 class="mb-3">New Announcement</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card p-4" style="max-width:650px;">
    <form method="POST" action="{{ route('superadmin.announcements.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-control" rows="3" required>{{ old('body') }}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Level</label>
                <select name="level" class="form-select">
                    <option value="info">Info (blue)</option>
                    <option value="warning">Warning (amber)</option>
                    <option value="success">Success (green)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Audience</label>
                <select name="audience" id="audienceSelect" class="form-select" onchange="document.getElementById('schoolPicker').style.display = this.value === 'specific' ? '' : 'none';">
                    <option value="all">All Schools</option>
                    <option value="specific">Specific Schools</option>
                </select>
            </div>
        </div>
        <div class="mb-3" id="schoolPicker" style="display:none;">
            <label class="form-label">Schools</label>
            <select name="school_ids[]" class="form-select" multiple size="6">
                @foreach($schools as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Hold Ctrl/Cmd to select more than one.</div>
        </div>
        <button class="btn btn-dark">Publish</button>
        <a href="{{ route('superadmin.announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

@endsection
