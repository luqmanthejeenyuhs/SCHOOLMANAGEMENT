<?php $__env->startSection('title', 'Activities'); ?>
<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Classes &amp; Activities</h3>
    <div class="d-flex gap-2 flex-wrap">
        <form method="GET" action="<?php echo e(route('admin.activities.index')); ?>" class="d-flex" style="min-width:260px;">
            <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control" placeholder="Search activities or patrons...">
            <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal"><i class="bi bi-plus-lg"></i> Add Activity</button>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('admin.classes.index')); ?>"><i class="bi bi-building"></i> Classes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="<?php echo e(route('admin.activities.index')); ?>"><i class="bi bi-trophy"></i> Activities</a>
    </li>
</ul>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Activity</th><th>Patron</th><th>Schedule</th><th>Participants</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><a href="<?php echo e(route('admin.activities.show', $activity)); ?>" class="fw-semibold text-decoration-none"><?php echo e($activity->name); ?></a></td>
                <td><?php echo e($activity->patron->user->name ?? '—'); ?></td>
                <td>
                    <?php if($activity->day_of_week): ?>
                        <?php echo e($activity->day_of_week); ?>

                        <?php if($activity->start_time && $activity->end_time): ?>
                            <br><span class="text-muted small"><?php echo e(\Carbon\Carbon::parse($activity->start_time)->format('g:i A')); ?>&ndash;<?php echo e(\Carbon\Carbon::parse($activity->end_time)->format('g:i A')); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">Not scheduled</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($activity->students_count); ?></td>
                <td>
                    <?php if($activity->isHappeningNow()): ?>
                        <span class="badge bg-success">Happening now</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Not now</span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <a href="<?php echo e(route('admin.activities.show', $activity)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <form action="<?php echo e(route('admin.activities.destroy', $activity)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this activity?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="text-center text-muted py-3"><?php echo e($search ? 'No activities match your search.' : 'No activities yet. Click "Add Activity" above.'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.activities.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">e.g. Swimming, Debate Club, Football.</p>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Activity name, e.g. Swimming" required>
                    </div>
                    <div class="mb-2">
                        <select name="patron_id" class="form-select">
                            <option value="">Patron / in charge (optional)</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="day_of_week" class="form-select">
                            <option value="">Day (optional)</option>
                            <option>Daily</option>
                            <?php $__currentLoopData = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option><?php echo e($day); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Start</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">End</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="venue" class="form-control" placeholder="Venue, e.g. School Pool">
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control" rows="2" placeholder="Notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/activities/index.blade.php ENDPATH**/ ?>