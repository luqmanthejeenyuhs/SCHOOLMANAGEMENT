<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PlatformInvoice;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

/**
 * The super_admin's landing page — platform-wide analytics, as opposed to
 * SchoolController@index which is the operational "manage one school at a
 * time" list. Every query here deliberately runs unscoped (no tenant
 * context) since this page's entire purpose is to see across every school
 * at once.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $schools = School::all();

        $schoolsByPlan = $schools->groupBy("plan")->map(fn ($rows) => $rows->count());

        // Signups over the last 6 months, oldest first — a simple growth
        // trend without needing a charting library on the backend.
        $signupTrend = collect(range(5, 0))->map(function ($monthsAgo) use ($schools) {
            $month = now()->subMonths($monthsAgo);

            return [
                "label" => $month->format("M Y"),
                "count" => $schools->filter(fn ($s) => $s->created_at->isSameMonth($month) && $s->created_at->isSameYear($month))->count(),
            ];
        });

        $trialsExpiringSoon = $schools->filter(fn ($s) => $s->plan === "trial" && $s->trial_ends_at && $s->trial_ends_at->between(now(), now()->addDays(7)))
            ->sortBy("trial_ends_at")
            ->values();

        $topSchoolsByStudents = School::withCount("students")->orderByDesc("students_count")->take(5)->get();

        $stats = [
            "total_schools" => $schools->count(),
            "active_schools" => $schools->where("is_active", true)->count(),
            "suspended_schools" => $schools->where("is_active", false)->count(),
            "total_students" => Student::allSchools()->count(),
            "total_teachers" => \App\Models\Teacher::allSchools()->count(),
            "total_users" => User::count(),
            "fee_payments_processed" => (float) Payment::allSchools()->sum("amount_paid"),
        ];

        $billing = [
            "outstanding" => (float) PlatformInvoice::where("status", "pending")->sum("amount"),
            "overdue_count" => PlatformInvoice::where("status", "pending")->where("due_date", "<", now())->count(),
            "collected_this_month" => (float) PlatformInvoice::where("status", "paid")
                ->whereBetween("paid_at", [now()->startOfMonth(), now()->endOfMonth()])
                ->sum("amount"),
        ];

        $recentActivity = AuditLog::with(["school", "user"])->latest("created_at")->take(10)->get();

        return view("superadmin.dashboard", compact(
            "stats", "schoolsByPlan", "signupTrend", "trialsExpiringSoon",
            "topSchoolsByStudents", "billing", "recentActivity"
        ));
    }
}
