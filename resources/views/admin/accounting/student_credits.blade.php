@extends('layouts.app')
@section('title', 'Student Credits')
@section('content')
<h3 class="mb-3">Student Credits</h3>
<p class="text-muted small">Money held on a student's behalf from an overpayment, waiting to be applied to their next invoice. This is a liability on your books (account 2000), not revenue — it only becomes revenue once it's actually applied to a fee.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Student</th><th>Admission No.</th><th class="text-end">Credit Balance</th></tr>
            </thead>
            <tbody>
            @forelse($credits as $credit)
                <tr>
                    <td>{{ optional($credit->student->user)->name }}</td>
                    <td>{{ $credit->student->admission_no }}</td>
                    <td class="text-end font-monospace fw-semibold">KES {{ number_format($credit->balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No students currently holding credit.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
