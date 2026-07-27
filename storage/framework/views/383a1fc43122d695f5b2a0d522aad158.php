<?php $__env->startSection('title', 'Timetable'); ?>
<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Timetable</h3>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" action="<?php echo e(route('admin.timetable.index')); ?>" class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">Viewing:</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($s->id); ?>" <?php echo e($sectionId == $s->id ? 'selected' : ''); ?>><?php echo e($s->schoolClass->name); ?> <?php echo e($s->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value="">No streams yet</option>
                <?php endif; ?>
            </select>
        </form>
        <?php if($section): ?>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addLessonModal"><i class="bi bi-plus-lg"></i> Add Lesson</button>
        <?php endif; ?>
    </div>
</div>

<?php if(! $section): ?>
    <div class="card p-4 text-center text-muted">Create a class and a stream first (under Classes), then come back here to build its timetable.</div>
<?php elseif($subjects->isEmpty()): ?>
    <div class="card p-4 text-center text-muted"><?php echo e($section->schoolClass->name); ?> <?php echo e($section->name); ?> has no subjects yet. Add subjects for this class first.</div>
<?php elseif($assignments->isEmpty()): ?>
    <div class="card p-4 text-center text-muted">No teacher is assigned to teach <?php echo e($section->schoolClass->name); ?> <?php echo e($section->name); ?> yet. Assign teachers to subjects for this class first, then come back here.</div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px;">Time</th>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($day); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $timeRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php [$start, $end] = explode('|', $range); ?>
                    <tr>
                        <td class="fw-semibold small"><?php echo e(\Carbon\Carbon::parse($start)->format('g:i A')); ?>&ndash;<?php echo e(\Carbon\Carbon::parse($end)->format('g:i A')); ?></td>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $slot = $slots->first(fn($s) => $s->day_of_week === $day && $s->start_time == $start && $s->end_time == $end); ?>
                            <td class="<?php echo e($slot ? 'bg-light' : ''); ?>">
                                <?php if($slot): ?>
                                    <div class="fw-semibold small"><?php echo e($slot->subject->name); ?></div>
                                    <div class="text-muted small"><?php echo e($slot->teacher->user->name); ?></div>
                                    <?php if($slot->room): ?><div class="text-muted small"><?php echo e($slot->room); ?></div><?php endif; ?>
                                    <form action="<?php echo e(route('admin.timetable.destroy', $slot)); ?>" method="POST" onsubmit="return confirm('Remove this lesson?');" class="mt-1">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="<?php echo e(count($days) + 1); ?>" class="text-center text-muted py-4">No lessons scheduled yet for <?php echo e($section->schoolClass->name); ?> <?php echo e($section->name); ?>. Click "Add Lesson" to start building the timetable.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Lesson Modal -->
    <div class="modal fade" id="addLessonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('admin.timetable.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section_id" value="<?php echo e($section->id); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Lesson — <?php echo e($section->schoolClass->name); ?> <?php echo e($section->name); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" id="subjectSelect" class="form-select" required>
                                <option value="">Select subject</option>
                                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Teacher</label>
                            <select name="teacher_id" id="teacherSelect" class="form-select" required>
                                <option value="">Select subject first</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" class="form-select" required>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($day); ?>"><?php echo e($day); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-0">Start</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-0">End</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Room (optional)</label>
                            <input type="text" name="room" class="form-control" placeholder="e.g. Room 4">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-dark">Add Lesson</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Subject -> Teacher is filtered client-side from this section's actual
    // class_subject_teacher assignments, so you can only pick a teacher who
    // is really assigned to teach that subject to this class.
    const assignments = <?php echo json_encode($assignments->map(fn($a) => ['subject_id' => $a->subject_id, 'teacher_id' => $a->teacher_id, 'teacher_name' => $a->teacher->user->name])) ?>;

    document.getElementById('subjectSelect').addEventListener('change', function () {
        const teacherSelect = document.getElementById('teacherSelect');
        const matches = assignments.filter(a => a.subject_id == this.value);
        teacherSelect.innerHTML = '<option value="">Select teacher</option>';
        matches.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.teacher_id;
            opt.textContent = a.teacher_name;
            teacherSelect.appendChild(opt);
        });
        if (matches.length === 0) {
            teacherSelect.innerHTML = '<option value="">No teacher assigned to this subject for this class</option>';
        }
    });
    </script>

    <?php if($errors->any()): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('addLessonModal')).show();
    });
    </script>
    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/timetable/index.blade.php ENDPATH**/ ?>