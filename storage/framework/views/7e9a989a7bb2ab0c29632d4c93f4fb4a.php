<?php $__env->startSection('title', 'Fee Types'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Fee Types</h3>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Fee Type</h6>
            <form method="POST" action="<?php echo e(route('admin.fee_types.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="e.g. Tuition Fee" required>
                </div>
                <div class="mb-2">
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount (KES)" required>
                </div>
                <div class="mb-2">
                    <select name="frequency" class="form-select" required>
                        <option value="term">Per Term</option>
                        <option value="month">Monthly</option>
                        <option value="year">Annual</option>
                        <option value="one_time">One-time</option>
                    </select>
                </div>
                <button class="btn btn-dark w-100">Add Fee Type</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Amount</th><th>Frequency</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $feeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feeType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($feeType->name); ?></td>
                        <td>KES <?php echo e(number_format($feeType->amount, 2)); ?></td>
                        <td><?php echo e(ucfirst(str_replace('_',' ', $feeType->frequency))); ?></td>
                        <td class="text-end">
                            <form action="<?php echo e(route('admin.fee_types.destroy', $feeType)); ?>" method="POST" onsubmit="return confirm('Delete this fee type?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No fee types yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/fees/index.blade.php ENDPATH**/ ?>