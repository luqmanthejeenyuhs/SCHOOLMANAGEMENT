<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            "students" => Student::count(),
            "teachers" => Teacher::count(),
            "classes" => SchoolClass::count(),
            "today_present" => Attendance::whereDate("date", today())->where("status", "present")->count(),
            "unpaid_invoices" => FeeInvoice::where("status", "!=", "paid")->count(),
            "collected_this_month" => FeeInvoice::with("payments")->get()->sum->totalPaid(),
        ];

        // Last 7 days of attendance, present vs absent per day.
        $attendanceTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);

            return [
                "label" => $date->format("D j"),
                "present" => Attendance::whereDate("date", $date)->where("status", "present")->count(),
                "absent" => Attendance::whereDate("date", $date)->where("status", "absent")->count(),
            ];
        })->values();

        // Students per class.
        $classDistribution = SchoolClass::withCount("students")
            ->orderBy("name")
            ->get()
            ->map(fn ($c) => ["label" => $c->name, "count" => $c->students_count])
            ->values();

        // Fees: collected vs still outstanding across all invoices, not just this month,
        // so the chart reflects the full picture rather than just $stats['collected_this_month'].
        $allInvoices = FeeInvoice::with("payments")->get();
        $feeSummary = [
            "collected" => (float) $allInvoices->sum->totalPaid(),
            "outstanding" => (float) $allInvoices->sum->balance(),
        ];

        return view("admin.dashboard", compact("stats", "attendanceTrend", "classDistribution", "feeSummary"));
    }
}
