@extends('layouts.app')
@section('title', 'Trial Balance')
@section('content')
<h3 class="mb-3">Trial Balance</h3>
<p class="text-muted small">If your books are correct, total debits and total credits below must be equal.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Code</th><th>Account</th><th>Type</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr>
            </thead>
            <tbody>
            @foreach($accounts as $row)
                <tr>
                    <td class="font-monospace">{{ $row['account']->code }}</td>
                    <td>{{ $row['account']->name }}</td>
                    <td class="text-capitalize text-muted small">{{ $row['account']->type }}</td>
                    <td class="text-end font-monospace">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                    <td class="text-end font-monospace">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="3" class="text-end">Totals</td>
                    <td class="text-end font-monospace">{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end font-monospace">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($totalDebit == $totalCredit)
    <div class="alert alert-success mt-3"><i class="bi bi-check-circle"></i> Books balance — debits equal credits.</div>
@else
    <div class="alert alert-danger mt-3"><i class="bi bi-exclamation-triangle"></i> Out of balance by KES {{ number_format(abs($totalDebit - $totalCredit), 2) }}. This shouldn't happen if all entries were posted through the system — check for a manually edited row in the database.</div>
@endif
@endsection
