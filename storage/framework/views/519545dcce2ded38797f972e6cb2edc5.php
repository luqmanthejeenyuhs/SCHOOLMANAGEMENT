<?php $__env->startSection('title', $student->user->name); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.classes.index')); ?>">Classes</a></li>
        <?php if($student->schoolClass): ?>
            <li class="breadcrumb-item"><a href="<?php echo e(route('admin.classes.show', $student->schoolClass)); ?>"><?php echo e($student->schoolClass->name); ?></a></li>
        <?php endif; ?>
        <?php if($student->section): ?>
            <li class="breadcrumb-item"><a href="<?php echo e(route('admin.sections.show', $student->section)); ?>"><?php echo e($student->section->name); ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?php echo e($student->admission_no); ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width:56px;height:56px;font-size:1.3rem;">
            <?php echo e(strtoupper(substr($student->user->name, 0, 1))); ?>

        </div>
        <div>
            <h3 class="mb-0"><?php echo e($student->user->name); ?></h3>
            <span class="text-muted">
                <?php echo e($student->admission_no); ?>

                <?php if($student->schoolClass): ?> &middot; <?php echo e($student->schoolClass->name); ?> <?php endif; ?>
                <?php if($student->section): ?> <?php echo e($student->section->name); ?> <?php endif; ?>
                <?php if($student->school_level): ?> &middot; <span class="badge bg-light text-dark border text-capitalize"><?php echo e($student->school_level); ?> School</span> <?php endif; ?>
            </span>
        </div>
    </div>
    <a href="<?php echo e(route('admin.students.edit', $student)); ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Fee Balance</span>
            <h4 class="mb-0 <?php echo e($feeBalance > 0 ? 'text-danger' : 'text-success'); ?>">KES <?php echo e(number_format($feeBalance, 2)); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Exams Recorded</span>
            <h4 class="mb-0"><?php echo e($examResults->flatten()->count()); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Attendance (30d)</span>
            <h4 class="mb-0"><?php echo e($attendanceRate !== null ? $attendanceRate.'%' : '—'); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <span class="text-muted small">Classmates in Stream</span>
            <h4 class="mb-0"><?php echo e($classmateCount ?? '—'); ?></h4>
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

    
    <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Bio Data</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:160px;">Full Name</th><td><?php echo e($student->user->name); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Email</th><td><?php echo e($student->user->email); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Date of Birth</th><td><?php echo e($student->dob?->format('d M Y') ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Address</th><td><?php echo e($student->address ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Admission No.</th><td><?php echo e($student->admission_no); ?></td></tr>
                        <tr><th class="text-muted fw-normal">UPI Number</th><td><?php echo e($student->upi_number ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Assessment No.</th><td><?php echo e($student->assessment_number ?? '—'); ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Guardian</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:160px;">Name</th><td><?php echo e($student->guardian_name ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Phone</th><td><?php echo e($student->guardian_phone ?? '—'); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-class" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card p-3">
                    <h6>Placement</h6>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-normal" style="width:140px;">Class</th><td><?php echo e($student->schoolClass->name ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Stream</th><td><?php echo e($student->section->name ?? 'Not assigned to a stream'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Pathway</th><td><?php echo e($student->pathway ?? '—'); ?></td></tr>
                        <tr><th class="text-muted fw-normal">Classmates</th><td><?php echo e($classmateCount ?? '—'); ?> other student<?php echo e($classmateCount == 1 ? '' : 's'); ?> in this stream</td></tr>
                    </table>
                    <?php if($student->section): ?>
                        <a href="<?php echo e(route('admin.sections.show', $student->section)); ?>" class="btn btn-sm btn-outline-secondary mt-2">View Full Stream Roster</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card p-3">
                    <h6>Subject Teachers</h6>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Subject</th><th>Teacher</th></tr></thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $subjectTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($assignment->subject->name ?? '—'); ?></td>
                                <td><?php echo e($assignment->teacher->user->name ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">No subject teachers assigned to this class/stream yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-exams" role="tabpanel">
        <div class="card p-3">
            <?php $__empty_1 = true; $__currentLoopData = $examResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $examName => $results): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-semibold"><?php echo e($examName); ?></div>
                        <?php $firstExam = $results->first()->exam; ?>
                        <?php if($firstExam): ?>
                            <a href="<?php echo e(route('admin.exams.report_card', [$firstExam, $student])); ?>" class="btn btn-sm btn-outline-primary">Full Report Card</a>
                        <?php endif; ?>
                    </div>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Subject</th><th>Marks</th><th>%</th><th>Grade</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($result->subject->name ?? '—'); ?></td>
                                <td><?php echo e($result->marks_obtained); ?> / <?php echo e($result->max_marks); ?></td>
                                <td><?php echo e($result->percentage()); ?>%</td>
                                <td><span class="badge bg-dark"><?php echo e($result->grade ?? '—'); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">No exam results recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-fees" role="tabpanel">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Total Billed</span><h5 class="mb-0">KES <?php echo e(number_format($totalBilled, 2)); ?></h5></div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Total Paid</span><h5 class="mb-0 text-success">KES <?php echo e(number_format($totalPaid, 2)); ?></h5></div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center"><span class="text-muted small">Balance</span><h5 class="mb-0 <?php echo e($feeBalance > 0 ? 'text-danger' : 'text-success'); ?>">KES <?php echo e(number_format($feeBalance, 2)); ?></h5></div>
            </div>
        </div>

        <div class="card p-3 mb-3">
            <h6>Invoices</h6>
            <table class="table table-sm mb-0 align-middle">
                <thead><tr><th>Invoice</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($invoice->id); ?></td>
                        <td>KES <?php echo e(number_format($invoice->amount, 2)); ?></td>
                        <td>KES <?php echo e(number_format($invoice->totalPaid(), 2)); ?></td>
                        <td>KES <?php echo e(number_format($invoice->balance(), 2)); ?></td>
                        <td><span class="badge bg-<?php echo e($invoice->status == 'paid' ? 'success' : ($invoice->status == 'partially_paid' ? 'warning' : 'secondary')); ?>"><?php echo e($invoice->status); ?></span></td>
                        <td class="text-end">
                            <?php if($invoice->status !== 'paid'): ?>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mpesaModal<?php echo e($invoice->id); ?>"><i class="bi bi-phone"></i> M-Pesa</button>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payModal<?php echo e($invoice->id); ?>">Record Payment</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No invoices yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card p-3">
            <h6>Payment History</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Invoice</th><th>Amount</th><th>Method</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($payment->payment_date->format('d M Y')); ?></td>
                        <td>#<?php echo e($payment->fee_invoice_id); ?></td>
                        <td>KES <?php echo e(number_format($payment->amount_paid, 2)); ?></td>
                        <td class="text-capitalize"><?php echo e($payment->method); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($invoice->status !== 'paid'): ?>
            <div class="modal fade" id="mpesaModal<?php echo e($invoice->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="<?php echo e(route('admin.invoices.mpesa_push', $invoice)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header"><h6 class="modal-title">Send M-Pesa STK Push — Invoice #<?php echo e($invoice->id); ?></h6></div>
                            <div class="modal-body">
                                <p class="small text-muted">Balance due: KES <?php echo e(number_format($invoice->balance(), 2)); ?>. The payer's phone will receive a prompt to enter their M-Pesa PIN.</p>
                                <label class="form-label small">Phone Number (format 2547XXXXXXXX)</label>
                                <input type="text" name="phone" class="form-control" placeholder="254712345678" pattern="2547[0-9]{8}" value="<?php echo e($student->guardian_phone); ?>" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success">Send STK Push</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="payModal<?php echo e($invoice->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="<?php echo e(route('admin.invoices.payments.store', $invoice)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header"><h6 class="modal-title">Record Payment — Invoice #<?php echo e($invoice->id); ?></h6></div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label class="form-label small">Amount Paid (balance: KES <?php echo e(number_format($invoice->balance(), 2)); ?>)</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
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
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Last 60 Days</h6>
                <span class="text-muted small">30-day rate: <?php echo e($attendanceRate !== null ? $attendanceRate.'%' : '—'); ?></span>
            </div>
            <?php if($attendance->isEmpty()): ?>
                <p class="text-muted mb-0">No attendance records in this period.</p>
            <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                    <?php $__currentLoopData = $attendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($record->date->format('D, d M Y')); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($record->status == 'present' ? 'success' : ($record->status == 'late' ? 'warning' : ($record->status == 'excused' ? 'info' : 'danger'))); ?>">
                                    <?php echo e(ucfirst($record->status)); ?>

                                </span>
                            </td>
                            <td class="text-muted small"><?php echo e($record->remarks ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-cbc" role="tabpanel">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Competency-Based Assessment</h6>
                <a href="<?php echo e(route('admin.cbc.report', $student)); ?>" class="btn btn-sm btn-outline-primary">Full CBC Report</a>
            </div>
            <?php if($cbcRecords->isEmpty()): ?>
                <p class="text-muted mb-0">No CBC competency records yet.</p>
            <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Sub-Strand</th><th>Term</th><th>Level</th><th>Remarks</th></tr></thead>
                    <tbody>
                    <?php $__currentLoopData = $cbcRecords->take(15); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($record->subStrand->name ?? '—'); ?></td>
                            <td><?php echo e($record->term); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e($record->performance_level); ?></span> <?php echo e($record->levelLabel()); ?></td>
                            <td class="text-muted small"><?php echo e($record->remarks ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if($cbcRecords->count() > 15): ?>
                    <p class="text-muted small mt-2 mb-0">Showing 15 most recent of <?php echo e($cbcRecords->count()); ?> — see the full CBC report for everything.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/students/show.blade.php ENDPATH**/ ?>