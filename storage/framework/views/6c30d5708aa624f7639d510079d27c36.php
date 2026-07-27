<?php $__env->startSection('title', 'Payslip'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3>Payslip</h3>
    <button class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card p-4" style="max-width:700px;">
    <div class="text-center mb-3">
        <h5 class="mb-0">PAYSLIP</h5>
        <small class="text-muted"><?php echo e($payslip->periodLabel()); ?></small>
    </div>

    <div class="row mb-3">
        <div class="col-6"><strong>Employee:</strong> <?php echo e($payslip->employee->name); ?></div>
        <div class="col-6"><strong>Job Title:</strong> <?php echo e($payslip->employee->job_title); ?></div>
        <div class="col-6 mt-1"><strong>KRA PIN:</strong> <?php echo e($payslip->employee->kra_pin ?? '—'); ?></div>
        <div class="col-6 mt-1"><strong>NSSF No:</strong> <?php echo e($payslip->employee->nssf_number ?? '—'); ?></div>
        <div class="col-6 mt-1"><strong>SHIF No:</strong> <?php echo e($payslip->employee->shif_number ?? '—'); ?></div>
    </div>

    <table class="table table-sm">
        <thead class="table-light"><tr><th>Earnings</th><th class="text-end">Amount (KES)</th></tr></thead>
        <tbody>
            <tr><td>Basic Salary</td><td class="text-end"><?php echo e(number_format($payslip->basic_salary, 2)); ?></td></tr>
            <tr><td>Allowances (House + Transport + Other)</td><td class="text-end"><?php echo e(number_format($payslip->allowances_total, 2)); ?></td></tr>
            <tr class="fw-bold"><td>Gross Pay</td><td class="text-end"><?php echo e(number_format($payslip->gross_pay, 2)); ?></td></tr>
        </tbody>
    </table>

    <table class="table table-sm">
        <thead class="table-light"><tr><th>Statutory Deductions</th><th class="text-end">Amount (KES)</th></tr></thead>
        <tbody>
            <tr><td>NSSF (Tier I &amp; II)</td><td class="text-end"><?php echo e(number_format($payslip->nssf, 2)); ?></td></tr>
            <tr><td>SHIF (2.75% of gross)</td><td class="text-end"><?php echo e(number_format($payslip->shif, 2)); ?></td></tr>
            <tr><td>Affordable Housing Levy (1.5% of gross)</td><td class="text-end"><?php echo e(number_format($payslip->housing_levy, 2)); ?></td></tr>
            <tr><td>PAYE (after personal relief of KES <?php echo e(number_format($payslip->personal_relief, 2)); ?>)</td><td class="text-end"><?php echo e(number_format($payslip->paye, 2)); ?></td></tr>
            <?php if($payslip->other_deductions > 0): ?>
            <tr><td>Other Deductions</td><td class="text-end"><?php echo e(number_format($payslip->other_deductions, 2)); ?></td></tr>
            <?php endif; ?>
            <tr class="fw-bold"><td>Total Deductions</td><td class="text-end"><?php echo e(number_format($payslip->total_deductions, 2)); ?></td></tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
        <strong>NET PAY</strong>
        <strong class="fs-4 text-success">KES <?php echo e(number_format($payslip->net_pay, 2)); ?></strong>
    </div>

    <p class="small text-muted mt-3 mb-0">
        Statutory rates (PAYE bands, SHIF, NSSF Tier I/II limits, Housing Levy) follow Kenya's 2024/2025
        framework and are configurable in <code>config/payroll.php</code>. Always verify current rates
        against KRA, NSSF, and SHIF publications before relying on this for real payroll runs.
    </p>
</div>

<style>
@media print {
    .navbar, .sidebar, .no-print { display: none !important; }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/payroll/payslip.blade.php ENDPATH**/ ?>