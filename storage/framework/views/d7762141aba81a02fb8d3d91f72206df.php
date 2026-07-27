<?php $__env->startSection('title', $class->name); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.classes.index')); ?>">Classes</a></li>
        <li class="breadcrumb-item active"><?php echo e($class->name); ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><?php echo e($class->name); ?></h3>
        <span class="text-muted"><?php echo e($class->students_count); ?> student<?php echo e($class->students_count == 1 ? '' : 's'); ?> across <?php echo e($sections->count()); ?> stream<?php echo e($sections->count() == 1 ? '' : 's'); ?></span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Add Stream</h6>
            <p class="text-muted small">e.g. <?php echo e($class->name); ?> East, <?php echo e($class->name); ?> West — add one card per stream so you can drill into each roster separately.</p>
            <form method="POST" action="<?php echo e(route('admin.sections.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="school_class_id" value="<?php echo e($class->id); ?>">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Stream name, e.g. East" required>
                    <button class="btn btn-dark">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3">
            <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-6">
                    <a href="<?php echo e(route('admin.sections.show', $section)); ?>" class="text-decoration-none text-reset">
                        <div class="card p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo e($class->name); ?> — <?php echo e($section->name); ?></h6>
                                    <span class="text-muted small"><?php echo e($section->students_count); ?> student<?php echo e($section->students_count == 1 ? '' : 's'); ?></span>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="card p-4 text-center text-muted">
                        No streams yet for <?php echo e($class->name); ?>. Add one on the left — useful when a grade has multiple parallel classes (e.g. Grade 10 East, West, North).
                    </div>
                </div>
            <?php endif; ?>

            <?php if($unassignedCount > 0): ?>
                <div class="col-12">
                    <div class="card p-3 border-warning-subtle bg-warning-subtle">
                        <span><?php echo e($unassignedCount); ?> student<?php echo e($unassignedCount == 1 ? ' is' : 's are'); ?> in <?php echo e($class->name); ?> but not yet assigned to a stream.</span>
                        <a href="<?php echo e(route('admin.students.index')); ?>" class="small">Assign them from the students list &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/classes/show.blade.php ENDPATH**/ ?>