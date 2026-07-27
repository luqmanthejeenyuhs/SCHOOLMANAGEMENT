<?php $__env->startSection('title', 'Classes & Activities'); ?>
<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Classes &amp; Activities</h3>
    <div class="d-flex gap-2 flex-wrap">
        <form method="GET" action="<?php echo e(route('admin.classes.index')); ?>" class="d-flex" style="min-width:260px;">
            <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control" placeholder="Search classes, streams or teachers...">
            <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addClassModal"><i class="bi bi-plus-lg"></i> Add Class</button>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addStreamModal"><i class="bi bi-plus-lg"></i> Add Stream</button>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="<?php echo e(route('admin.classes.index')); ?>"><i class="bi bi-building"></i> Classes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('admin.activities.index')); ?>"><i class="bi bi-trophy"></i> Activities</a>
    </li>
</ul>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Class</th><th>Stream</th><th>Class Teacher</th><th>Total Students</th><th></th></tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><a href="<?php echo e(route('admin.classes.show', $section->schoolClass)); ?>" class="text-decoration-none"><?php echo e($section->schoolClass->name); ?></a></td>
                <td>
                    <a href="<?php echo e(route('admin.sections.show', $section)); ?>" class="fw-semibold text-decoration-none">
                        <?php echo e($section->schoolClass->name); ?> <?php echo e($section->name); ?>

                    </a>
                </td>
                <td><?php echo e($section->classTeacher->user->name ?? '—'); ?></td>
                <td><?php echo e($section->students_count); ?></td>
                <td class="text-end">
                    <a href="<?php echo e(route('admin.sections.show', $section)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <form action="<?php echo e(route('admin.sections.destroy', $section)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this stream?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center text-muted py-3"><?php echo e($search ? 'No classes match your search.' : 'No streams yet. Use "Add Class" and "Add Stream" above.'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($classes->count()): ?>
<div class="card mt-3 p-3">
    <h6 class="mb-2">All Grades</h6>
    <div class="d-flex flex-wrap gap-2">
        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="d-flex align-items-center border rounded-pill ps-3 pe-1 py-1">
                <a href="<?php echo e(route('admin.classes.show', $class)); ?>" class="text-decoration-none me-2">
                    <?php echo e($class->name); ?> <span class="badge bg-secondary"><?php echo e($class->students_count); ?></span>
                </a>
                <form action="<?php echo e(route('admin.classes.destroy', $class)); ?>" method="POST" onsubmit="return confirm('Delete this class?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>


<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.classes.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">A grade/level, e.g. "Grade 9".</p>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Grade 11" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Class</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="addStreamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.sections.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Stream</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">A parallel class within a grade, e.g. "Green" or "A", with its class teacher.</p>
                    <div class="mb-2">
                        <select name="school_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Stream, e.g. Green" required>
                    </div>
                    <div class="mb-2">
                        <select name="class_teacher_id" class="form-select">
                            <option value="">Class teacher (optional)</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Stream</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/classes/index.blade.php ENDPATH**/ ?>