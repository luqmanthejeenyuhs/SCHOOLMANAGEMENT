<?php $__env->startSection('title', 'Staff Attendance'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Staff Attendance</h3>

<div class="card p-3 mb-3">
    <form method="GET" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label mb-1">Date</label>
            <input type="date" name="date" value="<?php echo e($date); ?>" class="form-control" onchange="this.form.submit()">
        </div>
        <button class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>Staff Name</th>
                <th>Job Title</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($record->employee->name ?? 'N/A'); ?></td>
                <td><?php echo e($record->employee->job_title ?? '-'); ?></td>
                <td><?php echo e($record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('g:i A') : '-'); ?></td>
                <td><?php echo e($record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('g:i A') : '-'); ?></td>
                <td><span class="badge bg-<?php echo e($record->status === 'present' ? 'success' : 'secondary'); ?>"><?php echo e(ucfirst($record->status)); ?></span></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center text-muted py-3">No attendance records for this date.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/staff_attendance/index.blade.php ENDPATH**/ ?>