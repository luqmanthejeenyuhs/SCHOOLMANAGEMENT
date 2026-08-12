<?php $__env->startSection('title', 'Admin Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="p-4 mb-4 rounded-4 text-white" style="background:linear-gradient(120deg,var(--brand-green-dark),var(--brand-green) 60%,var(--brand-green-mid));">
    <h3 class="mb-1 fw-bold"><i class="bi bi-speedometer2"></i> Admin Dashboard</h3>
    <div class="opacity-75 small">Welcome back — here's what's happening at your school today.</div>
</div>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Students</div>
            <div class="fs-2 fw-bold"><?php echo e($stats['students']); ?></div>
            <i class="bi bi-people"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Teachers</div>
            <div class="fs-2 fw-bold"><?php echo e($stats['teachers']); ?></div>
            <i class="bi bi-person-badge"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Classes</div>
            <div class="fs-2 fw-bold"><?php echo e($stats['classes']); ?></div>
            <i class="bi bi-building"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Present Today</div>
            <div class="fs-2 fw-bold"><?php echo e($stats['today_present']); ?></div>
            <i class="bi bi-calendar-check"></i>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small">Unpaid / Partially Paid Invoices</div>
            <div class="fs-2 fw-bold text-danger"><?php echo e($stats['unpaid_invoices']); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card p-3">
            <div class="text-muted small">Total Fees Collected</div>
            <div class="fs-2 fw-bold" style="color:var(--brand-green-dark);">KES <?php echo e(number_format($stats['collected_this_month'], 2)); ?></div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-dark me-2"><i class="bi bi-plus-lg"></i> Admit Student</a>
    <a href="<?php echo e(route('admin.teachers.create')); ?>" class="btn btn-outline-dark me-2"><i class="bi bi-plus-lg"></i> Add Teacher</a>
    <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-outline-dark"><i class="bi bi-receipt"></i> Manage Fees</a>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="card-header bg-transparent border-0 px-0 pt-0 fw-semibold" style="color:var(--brand-green-dark);">
                <i class="bi bi-graph-up"></i> Attendance — last 7 days
            </div>
            <canvas id="attendanceChart" height="140"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3">
            <div class="card-header bg-transparent border-0 px-0 pt-0 fw-semibold" style="color:var(--brand-green-dark);">
                <i class="bi bi-pie-chart"></i> Fees collected vs outstanding
            </div>
            <canvas id="feeChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-12">
        <div class="card p-3">
            <div class="card-header bg-transparent border-0 px-0 pt-0 fw-semibold" style="color:var(--brand-green-dark);">
                <i class="bi bi-bar-chart"></i> Students per class
            </div>
            <canvas id="classChart" height="90"></canvas>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const brandGreen = '#012622';
    const brandGreenMid = '#4D6764';
    const brandGold = getComputedStyle(document.documentElement).getPropertyValue('--brand-gold').trim() || '#C9972F';

    const attendanceLabels = <?php echo json_encode($attendanceTrend->pluck('label'), 15, 512) ?>;
    const presentData = <?php echo json_encode($attendanceTrend->pluck('present'), 15, 512) ?>;
    const absentData = <?php echo json_encode($attendanceTrend->pluck('absent'), 15, 512) ?>;

    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [
                {
                    label: 'Present',
                    data: presentData,
                    borderColor: brandGreen,
                    backgroundColor: brandGreen + '22',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Absent',
                    data: absentData,
                    borderColor: brandGold,
                    backgroundColor: brandGold + '22',
                    tension: 0.3,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    const feeCollected = <?php echo json_encode($feeSummary['collected'], 15, 512) ?>;
    const feeOutstanding = <?php echo json_encode($feeSummary['outstanding'], 15, 512) ?>;

    new Chart(document.getElementById('feeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Collected', 'Outstanding'],
            datasets: [{
                data: [feeCollected, feeOutstanding],
                backgroundColor: [brandGreen, brandGold],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
        },
    });

    const classLabels = <?php echo json_encode($classDistribution->pluck('label'), 15, 512) ?>;
    const classCounts = <?php echo json_encode($classDistribution->pluck('count'), 15, 512) ?>;

    new Chart(document.getElementById('classChart'), {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Students',
                data: classCounts,
                backgroundColor: brandGreenMid,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>