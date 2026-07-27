<?php $__env->startSection('title', 'Grading Scale'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Grading Scale</h3>
    <a href="<?php echo e(route('admin.exams.index')); ?>" class="btn btn-outline-secondary btn-sm">Back to Exams</a>
</div>
<p class="text-muted">These bands decide the letter grade shown for every exam result and report card, based on percentage score. Ranges must not overlap.</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Grade Band</h6>
            <form method="POST" action="<?php echo e(route('admin.grading_scales.store')); ?>">
                <?php echo csrf_field(); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $scales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="fw-bold"><?php echo e($scale->grade); ?></td>
                        <td><?php echo e(rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.')); ?>% – <?php echo e(rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.')); ?>%</td>
                        <td><?php echo e($scale->points ?? '—'); ?></td>
                        <td><?php echo e($scale->remark ?? '—'); ?></td>
                        <td class="text-end">
                            <form action="<?php echo e(route('admin.grading_scales.destroy', $scale)); ?>" method="POST" onsubmit="return confirm('Delete this grade band?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No grade bands configured — results won't show a letter grade until you add some.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/grading_scales/index.blade.php ENDPATH**/ ?>