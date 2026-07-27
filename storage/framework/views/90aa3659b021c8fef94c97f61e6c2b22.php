<?php $__env->startSection('title', 'Exams'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Exams &amp; Results</h3>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.grading_scales.index')); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders"></i> Grading Scale</a>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createExamModal"><i class="bi bi-plus-lg"></i> Create Exam</button>
    </div>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Exam</th><th>Class</th><th>Term</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($exam->name); ?></td>
                <td><?php echo e($exam->schoolClass->name); ?></td>
                <td><?php echo e($exam->term ?? '—'); ?></td>
                <td><?php echo e($exam->exam_date?->format('d M Y') ?? '—'); ?></td>
                <td class="text-end">
                    <a href="<?php echo e(route('admin.exams.results', $exam)); ?>" class="btn btn-sm btn-outline-primary">View Results</a>
                    <form action="<?php echo e(route('admin.exams.destroy', $exam)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No exams yet. Click "Create Exam" to add one.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="createExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.exams.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Create Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Exam Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Mid-Term Exam" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Class</label>
                        <select name="school_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Term (optional)</label>
                        <input type="text" name="term" class="form-control" placeholder="e.g. Term 2">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Exam Date (optional)</label>
                        <input type="date" name="exam_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Create Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/exams/index.blade.php ENDPATH**/ ?>