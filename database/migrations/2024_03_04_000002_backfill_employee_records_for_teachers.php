<?php

use App\Models\Employee;
use App\Models\Teacher;
use App\Support\Facades\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Employee records are now created automatically alongside every new
     * teacher (see Admin\TeacherController::store) — this is the one-time
     * backfill for teachers added before that existed.
     *
     * Everyone found here defaults to employment_type = "full_time" since
     * there's no way to know from existing data who was actually an intern
     * or volunteer — review Payroll > Employees afterward and correct
     * anyone that's wrong.
     *
     * Teacher::allSchools() is required here, not optional: this migration
     * runs via `php artisan migrate` with no logged-in user, so no tenant
     * is resolved — a plain Teacher::get() would run through the normal
     * tenant scope, which fails CLOSED with nothing resolved and would
     * silently find zero teachers across every school. See the Account.php
     * fix from earlier for the same underlying issue.
     */
    public function up(): void
    {
        Teacher::allSchools()->with('user')->get()->each(function (Teacher $teacher) {
            Tenant::runFor($teacher->school_id, function () use ($teacher) {
                $alreadyLinked = Employee::where('teacher_id', $teacher->id)
                    ->orWhere('user_id', $teacher->user_id)
                    ->exists();

                if ($alreadyLinked) {
                    return;
                }

                Employee::create([
                    'user_id' => $teacher->user_id,
                    'teacher_id' => $teacher->id,
                    'name' => $teacher->user->name ?? 'Unknown',
                    'job_title' => 'Teacher',
                    'employment_type' => 'full_time',
                    'is_teaching_staff' => true,
                    'phone' => $teacher->user->phone ?? null,
                    'employment_date' => $teacher->joining_date,
                    'basic_salary' => 0,
                ]);
            });
        });
    }

    public function down(): void
    {
        // Intentionally left as a no-op — these Employee records may
        // already have attendance/payslips posted against them by the
        // time anyone considers rolling this back.
    }
};
