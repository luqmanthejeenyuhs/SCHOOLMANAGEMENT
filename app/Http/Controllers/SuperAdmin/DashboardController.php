<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\School;
use App\Models\Scopes\TenantScope;
use App\Models\Student;
use App\Models\Teacher;

/**
 * Platform-level overview — totals, growth, and revenue across every
 * school, not any one school's own dashboard.
 */
class DashboardController extends Controller
{
    // Your current pricing: per-student, billed monthly. This is a
    // stopgap ahead of a real subscription/plan system — when that
    // exists, swap this constant for a per-school rate/plan lookup
    // instead of a single global number.
    public const RATE_PER_STUDENT_KES = 100;

    // How many months of signup history to show in the growth chart.
    protected const GROWTH_MONTHS = 6;

    public function index()
    {
        $totalSchools = School::count();
        $activeSchools = School::where('is_active', true)->count();
        $suspendedSchools = $totalSchools - $activeSchools;

        // Student/Teacher/Employee are tenant-scoped models — a super_admin
        // has no school_id, so with no tenant resolved that scope fails
        // closed. allSchools() is what makes these real platform-wide
        // totals instead of silently zero.
        $totalStudents = Student::allSchools()->count();
        $totalTeachers = Teacher::allSchools()->count();
        $totalStaff = Employee::allSchools()->where('is_active', true)->count();

        $newSchoolsThisMonth = School::where('created_at', '>=', now()->startOfMonth())->count();

        $trialsExpiringSoon = School::whereNotNull('trial_ends_at')
            ->where('is_active', true)
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->orderBy('trial_ends_at')
            ->get();

        // Signups per month, oldest to newest, for the growth chart.
        $growth = collect(range(self::GROWTH_MONTHS - 1, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M Y'),
                'count' => School::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });

        // Per-school breakdown, with an estimated monthly bill from your
        // actual pricing model — suspended schools aren't billed.
        $schools = School::withCount([
            'students' => fn ($q) => $q->withoutGlobalScope(TenantScope::class),
        ])
            ->orderByDesc('students_count')
            ->get()
            ->map(function ($school) {
                $school->estimated_monthly_bill = $school->is_active
                    ? $school->students_count * self::RATE_PER_STUDENT_KES
                    : 0;

                return $school;
            });

        $estimatedMonthlyRevenue = $schools->sum('estimated_monthly_bill');

        return view('superadmin.dashboard', compact(
            'totalSchools', 'activeSchools', 'suspendedSchools',
            'totalStudents', 'totalTeachers', 'totalStaff',
            'newSchoolsThisMonth', 'trialsExpiringSoon', 'growth',
            'schools', 'estimatedMonthlyRevenue'
        ))->with('ratePerStudent', self::RATE_PER_STUDENT_KES);
    }
}
