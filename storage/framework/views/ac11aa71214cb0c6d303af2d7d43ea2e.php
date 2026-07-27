<?php $__env->startSection('title', 'Staff & Payroll'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Staff &amp; Payroll</h3>
    <div>
        <a href="<?php echo e(route('admin.payslips.index')); ?>" class="btn btn-outline-dark me-2">View Payslips</a>
        <a href="<?php echo e(route('admin.employees.create')); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Employee</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Job Title</th><th>KRA PIN</th><th>Basic Salary</th><th>Gross Pay</th><th></th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($employee->name); ?> <?php if($employee->is_teaching_staff): ?><span class="badge bg-info text-dark">Teaching</span><?php endif; ?></td>
                    <td><?php echo e($employee->job_title); ?></td>
                    <td><?php echo e($employee->kra_pin ?? '—'); ?></td>
                    <td>KES <?php echo e(number_format($employee->basic_salary, 2)); ?></td>
                    <td>KES <?php echo e(number_format($employee->grossPay(), 2)); ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payslipModal<?php echo e($employee->id); ?>">Generate Payslip</button>
                        <form action="<?php echo e(route('admin.employees.destroy', $employee)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>

                        <div class="modal fade" id="payslipModal<?php echo e($employee->id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo e(route('admin.payslips.generate', $employee)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="modal-header"><h6 class="modal-title">Generate Payslip — <?php echo e($employee->name); ?></h6></div>
                                        <div class="modal-body row g-2">
                                            <div class="col-6">
                                                <label class="form-label small">Month</label>
                                                <select name="month" class="form-select">
                                                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($m); ?>" <?php if($m == now()->month): echo 'selected'; endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Year</label>
                                                <input type="number" name="year" class="form-control" value="<?php echo e(now()->year); ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Generate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No employees yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3"><?php echo e($employees->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/payroll/employees/index.blade.php ENDPATH**/ ?>