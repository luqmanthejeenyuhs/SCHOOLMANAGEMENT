<?php $__env->startSection('title', 'Subjects'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Subjects</h3>
    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal"><i class="bi bi-plus-lg"></i> Add Subject</button>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>Class</th><th>Subject</th><th>Code</th><th></th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($subject->schoolClass->name); ?></td>
                <td><a href="<?php echo e(route('admin.subjects.show', $subject)); ?>" class="text-decoration-none fw-semibold"><?php echo e($subject->name); ?></a></td>
                <td><?php echo e($subject->code ?? '—'); ?></td>
                <td class="text-end">
                    <form action="<?php echo e(route('admin.subjects.destroy', $subject)); ?>" method="POST" onsubmit="return confirm('Delete this subject?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No subjects yet. Click "Add Subject" to create one.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.subjects.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                        <label class="form-label small">Subject Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Physics" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Code (optional)</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. PHY">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/subjects/index.blade.php ENDPATH**/ ?>