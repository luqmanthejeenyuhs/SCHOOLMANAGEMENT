<?php $__env->startSection('title', 'Sections'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Sections</h3>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Section</h6>
            <form method="POST" action="<?php echo e(route('admin.sections.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-2">
                    <select name="school_class_id" class="form-select" required>
                        <option value="">Select class</option>
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="e.g. A" required>
                </div>
                <button class="btn btn-dark w-100">Add Section</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Class</th><th>Section</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($section->schoolClass->name); ?></td>
                        <td><?php echo e($section->name); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('admin.sections.show', $section)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <form action="<?php echo e(route('admin.sections.destroy', $section)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">No sections yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/sections/index.blade.php ENDPATH**/ ?>