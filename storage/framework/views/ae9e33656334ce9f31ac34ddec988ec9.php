<?php $__env->startSection('title', $activity->name); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.activities.index')); ?>">Activities</a></li>
        <li class="breadcrumb-item active"><?php echo e($activity->name); ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">
            <?php echo e($activity->name); ?>

            <?php if($activity->isHappeningNow()): ?>
                <span class="badge bg-success align-middle">Happening now</span>
            <?php endif; ?>
        </h3>
        <div class="text-muted">
            Patron: <?php echo e($activity->patron->user->name ?? 'Not assigned'); ?>

            <?php if($activity->venue): ?> &middot; <?php echo e($activity->venue); ?> <?php endif; ?>
        </div>
        <div class="text-muted small">
            <?php if($activity->day_of_week): ?>
                <?php echo e($activity->day_of_week); ?>

                <?php if($activity->start_time && $activity->end_time): ?>
                    , <?php echo e(\Carbon\Carbon::parse($activity->start_time)->format('g:i A')); ?>&ndash;<?php echo e(\Carbon\Carbon::parse($activity->end_time)->format('g:i A')); ?>

                <?php endif; ?>
            <?php else: ?>
                Not scheduled yet
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($activity->description): ?>
<div class="card p-3 mb-3"><?php echo e($activity->description); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Sign Up a Student</h6>
            <form method="POST" action="<?php echo e(route('admin.activities.students.store', $activity)); ?>">
                <?php echo csrf_field(); ?>
                <select name="student_id" class="form-select mb-2" required>
                    <option value="">Select student</option>
                    <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>"><?php echo e($student->admission_no); ?> &mdash; <?php echo e($student->user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="btn btn-dark w-100">Sign Up</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Signed-up Students (<?php echo e($students->count()); ?>)</div>
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Admission No</th><th>Name</th><th>Signed Up</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($student->admission_no); ?></td>
                        <td><?php echo e($student->user->name); ?></td>
                        <td><?php echo e($student->pivot->signed_up_at ? \Carbon\Carbon::parse($student->pivot->signed_up_at)->format('d M Y') : '—'); ?></td>
                        <td class="text-end">
                            <form action="<?php echo e(route('admin.activities.students.destroy', [$activity, $student])); ?>" method="POST" onsubmit="return confirm('Remove this student from the activity?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No students signed up yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/activities/show.blade.php ENDPATH**/ ?>