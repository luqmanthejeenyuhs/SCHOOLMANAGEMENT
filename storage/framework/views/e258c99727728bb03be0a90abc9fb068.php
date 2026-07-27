<?php $__env->startSection('title', 'Bank & M-Pesa Ledger'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-1">Bank &amp; M-Pesa Ledger</h3>
<p class="text-muted small">Every deposit that lands here came in automatically — no bank slips, no manual receipt books. Parents deposit at any branch/agent or pay via Paybill using the student's <strong>Admission Number</strong> as the reference, and the webhook below credits the invoice and texts a receipt instantly.</p>

<div class="row g-3 mb-2">
    <div class="col-md-6">
        <div class="card p-3">
            <div class="text-muted small">Bank Webhook URL</div>
            <code class="small"><?php echo e(route('webhooks.bank.deposit')); ?></code>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <div class="text-muted small">M-Pesa C2B Confirmation URL (register on Daraja)</div>
            <code class="small"><?php echo e(route('mpesa.c2b.confirmation')); ?></code>
        </div>
    </div>
</div>

<h5 class="mt-4">Bank Deposits</h5>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Bank</th><th>Reference</th><th>Account Ref (typed)</th><th>Amount</th><th>Student</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bankTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($tx->bank_name); ?></td>
                    <td class="small"><?php echo e($tx->bank_reference); ?></td>
                    <td><?php echo e($tx->account_reference); ?></td>
                    <td>KES <?php echo e(number_format($tx->amount, 2)); ?></td>
                    <td><?php echo e($tx->student->user->name ?? '—'); ?></td>
                    <td>
                        <?php if($tx->status === 'matched'): ?>
                            <span class="badge bg-success">Matched</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Unmatched</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if($tx->status !== 'matched'): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bankReconcile<?php echo e($tx->id); ?>">Reconcile</button>
                        <div class="modal fade" id="bankReconcile<?php echo e($tx->id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo e(route('admin.finance.ledger.bank.reconcile', $tx)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="modal-header"><h6 class="modal-title">Reconcile KES <?php echo e(number_format($tx->amount,2)); ?> — <?php echo e($tx->bank_reference); ?></h6></div>
                                        <div class="modal-body">
                                            <label class="form-label small">The depositor typed "<?php echo e($tx->account_reference); ?>" — match to the correct admission number:</label>
                                            <input type="text" name="admission_no" class="form-control" placeholder="ADM-0001" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Match &amp; Credit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No bank deposits received yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2"><?php echo e($bankTransactions->links()); ?></div>

<h5 class="mt-4">M-Pesa Paybill (C2B) Payments</h5>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Transaction ID</th><th>Phone</th><th>Account Ref (typed)</th><th>Amount</th><th>Student</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $mpesaTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="small"><?php echo e($tx->transaction_id); ?></td>
                    <td><?php echo e($tx->msisdn); ?></td>
                    <td><?php echo e($tx->bill_ref_number); ?></td>
                    <td>KES <?php echo e(number_format($tx->amount, 2)); ?></td>
                    <td><?php echo e($tx->student->user->name ?? '—'); ?></td>
                    <td>
                        <?php if($tx->status === 'matched'): ?>
                            <span class="badge bg-success">Matched</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Unmatched</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if($tx->status !== 'matched'): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mpesaReconcile<?php echo e($tx->id); ?>">Reconcile</button>
                        <div class="modal fade" id="mpesaReconcile<?php echo e($tx->id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo e(route('admin.finance.ledger.mpesa.reconcile', $tx)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="modal-header"><h6 class="modal-title">Reconcile KES <?php echo e(number_format($tx->amount,2)); ?> — <?php echo e($tx->transaction_id); ?></h6></div>
                                        <div class="modal-body">
                                            <label class="form-label small">The payer typed "<?php echo e($tx->bill_ref_number); ?>" at the Paybill menu — match to the correct admission number:</label>
                                            <input type="text" name="admission_no" class="form-control" placeholder="ADM-0001" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Match &amp; Credit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No M-Pesa Paybill payments received yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2"><?php echo e($mpesaTransactions->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/finance/ledger.blade.php ENDPATH**/ ?>