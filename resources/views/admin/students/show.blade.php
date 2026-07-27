@extends('layouts.app')
@section('title', $student->user->name)
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
        @if($student->schoolClass)
            <li class="breadcrumb-item"><a href="{{ route('admin.classes.show', $student->schoolClass) }}">{{ $student->schoolClass->name }}</a></li>
        @endif
        @if($student->section)
            <li class="breadcrumb-item"><a href="{{ route('admin.sections.show', $student->section) }}">{{ $student->section->name }}</a></li>
        @endif
        <li class="breadcrumb-item active">{{ $student->admission_no }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width:56px;height:56px;font-size:1.3rem;">
            {{ strtoupper(substr($student->user->name, 0, 1)) }}
        </div>
        <div>
            <h3 class="mb-0">{{ $student->user->name }}</h3>
            <span class="text-muted">
                {{ $student->admission_no }}
                @if($student->schoolClass) &middot; {{ $student->schoolClass->name }} @endif
                @if($student->section) {{ $student->section->name }} @endif
                @if($student->school_level) &middot; <span class="badge bg-light text-dark border text-capitalize">{{ $student->school_level }} School</span> @endif
            </span>
        </div>
    </div>
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Fee Balance</span>
            <h4 class="mb-0 {{ $feeBalance > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($feeBalance, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Exams Recorded</span>
            <h4 class="mb-0">{{ $examResults->flatten()->count() }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Attendance (30d)</span>
            <h4 class="mb-0">{{ $attendanceRate !== null ? $attendanceRate.'%' : '—' }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Classmates in Stream</span>
            <h4 class="mb-0">{{ $classmateCount ?? '—' }}</h4>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="studentTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-info-btn" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab"><i class="bi bi-person"></i> Information</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-class-btn" data-bs-toggle="tab" data-bs-target="#tab-class" type="button" role="tab"><i class="bi bi-building"></i> Class</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-exams-btn" data-bs-toggle="tab" data-bs-target="#tab-exams" type="button" role="tab"><i class="bi bi-clipboard-check"></i> Exams</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-fees-btn" data-bs-toggle="tab" data-bs-target="#tab-fees" type="button" role="tab"><i class="bi bi-cash-coin"></i> Fees</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-attendance-btn" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab"><i class="bi bi-calendar-check"></i> Attendance</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-cbc-btn" data-bs-toggle="tab" data-bs-target="#tab-cbc" type="button" role="tab"><i class="bi bi-award"></i> CBC</button>
    </li>
</ul>

<div class="tab-content">

    {{-- INFORMATION --}}
    <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Bio Data</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:160px;">Full Name</th><td>{{ $student->user->name }}</td></tr>
                        <tr><th class="text-muted fw-normal">Email</th><td>{{ $student->user->email }}</td></tr>
                        <tr><th class="text-muted fw-normal">Date of Birth</th><td>{{ $student->dob?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Address</th><td>{{ $student->address ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Admission No.</th><td>{{ $student->admission_no }}</td></tr>
                        <tr><th class="text-muted fw-normal">UPI Number</th><td>{{ $student->upi_number ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Assessment No.</th><td>{{ $student->assessment_number ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Guardian</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:160px;">Name</th><td>{{ $student->guardian_name ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Phone</th><td>{{ $student->guardian_phone ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CLASS --}}
    <div class="tab-pane fade" id="tab-class" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card p-3">
                    <h6>Placement</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:140px;">Class</th><td>{{ $student->schoolClass->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Stream</th><td>{{ $student->section->name ?? 'Not assigned to a stream' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Pathway</th><td>{{ $student->pathway ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Classmates</th><td>{{ $classmateCount ?? '—' }} other student{{ $classmateCount == 1 ? '' : 's' }} in this stream</td></tr>
                    </table>
                    @if($student->section)
                        <a href="{{ route('admin.sections.show', $student->section) }}" class="btn btn-sm btn-outline-secondary mt-2">View Full Stream Roster</a>
                    @endif
                </div>
            </div>
            <div class="col-md-7">
                <div class="card p-3">
                    <h6>Subject Teachers</h6>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Subject</th><th>Teacher</th></tr></thead>
                        <tbody>
                        @forelse($subjectTeachers as $assignment)
                            <tr>
                                <td>{{ $assignment->subject->name ?? '—' }}</td>
                                <td>{{ $assignment->teacher->user->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No subject teachers assigned to this class/stream yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- EXAMS --}}
    <div class="tab-pane fade" id="tab-exams" role="tabpanel">
        <div class="card p-3">
            @forelse($examResults as $examName => $results)
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-semibold">{{ $examName }}</div>
                        @php $firstExam = $results->first()->exam; @endphp
                        @if($firstExam)
                            <a href="{{ route('admin.exams.report_card', [$firstExam, $student]) }}" class="btn btn-sm btn-outline-primary">Full Report Card</a>
                        @endif
                    </div>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Subject</th><th>Marks</th><th>%</th><th>Grade</th></tr></thead>
                        <tbody>
                        @foreach($results as $result)
                            <tr>
                                <td>{{ $result->subject->name ?? '—' }}</td>
                                <td>{{ $result->marks_obtained }} / {{ $result->max_marks }}</td>
                                <td>{{ $result->percentage() }}%</td>
                                <td><span class="badge bg-dark">{{ $result->grade ?? '—' }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-muted mb-0">No exam results recorded yet.</p>
            @endforelse
        </div>
    </div>

    {{-- FEES --}}
    <div class="tab-pane fade" id="tab-fees" role="tabpanel">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Total Billed</span><h5 class="mb-0">KES {{ number_format($totalBilled, 2) }}</h5></div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Total Paid</span><h5 class="mb-0 text-success">KES {{ number_format($totalPaid, 2) }}</h5></div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Balance</span><h5 class="mb-0 {{ $feeBalance > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($feeBalance, 2) }}</h5></div>
            </div>
        </div>

        <div class="card p-3 mb-3">
            <h6>Invoices</h6>
            <table class="table table-sm mb-0 align-middle">
                <thead><tr><th>Invoice</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>#{{ $invoice->id }}</td>
                        <td>KES {{ number_format($invoice->amount, 2) }}</td>
                        <td>KES {{ number_format($invoice->totalPaid(), 2) }}</td>
                        <td>KES {{ number_format($invoice->balance(), 2) }}</td>
                        <td><span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'partially_paid' ? 'warning' : 'secondary') }}">{{ $invoice->status }}</span></td>
                        <td class="text-end">
                            @if($invoice->status !== 'paid')
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mpesaModal{{ $invoice->id }}"><i class="bi bi-phone"></i> M-Pesa</button>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payModal{{ $invoice->id }}">Record Payment</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card p-3">
            <h6>Payment History</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Invoice</th><th>Amount</th><th>Method</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>#{{ $payment->fee_invoice_id }}</td>
                        <td>KES {{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="text-capitalize">{{ $payment->method }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modals: M-Pesa push + manual payment recording, per invoice --}}
        @foreach($invoices as $invoice)
            @if($invoice->status !== 'paid')
            <div class="modal fade" id="mpesaModal{{ $invoice->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.invoices.mpesa_push', $invoice) }}">
                            @csrf
                            <div class="modal-header"><h6 class="modal-title">Send M-Pesa STK Push — Invoice #{{ $invoice->id }}</h6></div>
                            <div class="modal-body">
                                <p class="small text-muted">Balance due: KES {{ number_format($invoice->balance(), 2) }}. The payer's phone will receive a prompt to enter their M-Pesa PIN.</p>
                                <label class="form-label small">Phone Number (format 2547XXXXXXXX)</label>
                                <input type="text" name="phone" class="form-control" placeholder="254712345678" pattern="2547[0-9]{8}" value="{{ $student->guardian_phone }}" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success">Send STK Push</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="payModal{{ $invoice->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}">
                            @csrf
                            <div class="modal-header"><h6 class="modal-title">Record Payment — Invoice #{{ $invoice->id }}</h6></div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label class="form-label small">Amount Paid (balance: KES {{ number_format($invoice->balance(), 2) }})</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Method</label>
                                    <select name="method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="mpesa">M-Pesa</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-dark">Save Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- ATTENDANCE --}}
    <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Last 60 Days</h6>
                <span class="text-muted small">30-day rate: {{ $attendanceRate !== null ? $attendanceRate.'%' : '—' }}</span>
            </div>
            @if($attendance->isEmpty())
                <p class="text-muted mb-0">No attendance records in this period.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @foreach($attendance as $record)
                        <tr>
                            <td>{{ $record->date->format('D, d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $record->status == 'present' ? 'success' : ($record->status == 'late' ? 'warning' : ($record->status == 'excused' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- CBC --}}
    <div class="tab-pane fade" id="tab-cbc" role="tabpanel">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Competency-Based Assessment</h6>
                <a href="{{ route('admin.cbc.report', $student) }}" class="btn btn-sm btn-outline-primary">Full CBC Report</a>
            </div>
            @if($cbcRecords->isEmpty())
                <p class="text-muted mb-0">No CBC competency records yet.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Sub-Strand</th><th>Term</th><th>Level</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @foreach($cbcRecords->take(15) as $record)
                        <tr>
                            <td>{{ $record->subStrand->name ?? '—' }}</td>
                            <td>{{ $record->term }}</td>
                            <td><span class="badge bg-secondary">{{ $record->performance_level }}</span> {{ $record->levelLabel() }}</td>
                            <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if($cbcRecords->count() > 15)
                    <p class="text-muted small mt-2 mb-0">Showing 15 most recent of {{ $cbcRecords->count() }} — see the full CBC report for everything.</p>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection
