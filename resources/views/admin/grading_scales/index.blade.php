@extends('layouts.app')
@section('title', 'Grading Scale')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Grading Scale</h3>
    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary btn-sm">Back to Exams</a>
</div>
<p class="text-muted">These bands decide the letter grade shown for every exam result and report card, based on percentage score. Ranges must not overlap.</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Grade Band</h6>
            <form method="POST" action="{{ route('admin.grading_scales.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Grade</label>
                    <input type="text" name="grade" class="form-control" placeholder="e.g. A" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small">Min % (inclusive)</label>
                        <input type="number" step="0.01" name="min_score" class="form-control" placeholder="80" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Max % (inclusive)</label>
                        <input type="number" step="0.01" name="max_score" class="form-control" placeholder="100" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Points (optional, for mean grade point)</label>
                    <input type="number" step="0.1" name="points" class="form-control" placeholder="12">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Remark (optional)</label>
                    <input type="text" name="remark" class="form-control" placeholder="e.g. Excellent">
                </div>
                <button class="btn btn-dark w-100">Add Band</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Grade</th><th>Range</th><th>Points</th><th>Remark</th><th></th></tr></thead>
                <tbody>
                @forelse($scales as $scale)
                    <tr>
                        <td class="fw-bold">{{ $scale->grade }}</td>
                        <td>{{ rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.') }}% – {{ rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.') }}%</td>
                        <td>{{ $scale->points ?? '—' }}</td>
                        <td>{{ $scale->remark ?? '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.grading_scales.destroy', $scale) }}" method="POST" onsubmit="return confirm('Delete this grade band?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No grade bands configured — results won't show a letter grade until you add some.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
