<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;
use App\Support\Facades\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        // Fee figures used to be computed by loading every invoice (with
        // every payment) for the school into PHP and summing there — for
        // a school running for a few years that's easily tens of
        // thousands of rows pulled into memory on every single dashboard
        // view, by every admin. These are now plain SQL aggregates: the
        // database does the summing, only a handful of numbers cross the
        // wire. Cached briefly since the dashboard is the highest-traffic
        // page in the app and these totals don't need to be second-fresh.
        $feeSummary = Cache::remember("dashboard:fee_summary:".Tenant::id(), now()->addMinutes(5), function () {
            $collected = (float) Payment::sum("amount_paid");
            $billed = (float) FeeInvoice::sum("amount");

            return [
                "collected" => $collected,
                "outstanding" => max($billed - $collected, 0),
            ];
        });

        $stats = [
            "students" => Student::count(),
            "teachers" => Teacher::count(),
            "classes" => SchoolClass::count(),
            // Morning is treated as the canonical "attended today" mark for
            // rate/summary purposes — a student can now also be marked for
            // the afternoon separately (see Teacher\AttendanceController),
            // but that's for catching "came in fine, went home sick after
            // lunch" cases, not for counting as a second day.
            "today_present" => Attendance::whereDate("date", today())->where("session", "morning")->where("status", "present")->count(),
            "unpaid_invoices" => FeeInvoice::where("status", "!=", "paid")->count(),
            // A plain date-range comparison (rather than whereMonth/
            // whereYear, which wrap the column in a function and stop
            // MySQL from using an index on it) so this stays fast as
            // payment history grows.
            "collected_this_month" => Payment::where("payment_date", ">=", now()->startOfMonth())
                ->where("payment_date", "<", now()->addMonthNoOverflow()->startOfMonth())
                ->sum("amount_paid"),
        ];

        // Last 7 days of attendance, present vs absent per day. One
        // grouped query instead of 14 separate ones.
        $attendanceCounts = Attendance::selectRaw("date, status, COUNT(*) as total")
            ->where("date", ">=", today()->subDays(6))
            ->where("session", "morning")
            ->groupBy("date", "status")
            ->get()
            ->groupBy(fn ($row) => $row->date->toDateString());

        $attendanceTrend = collect(range(6, 0))->map(function ($daysAgo) use ($attendanceCounts) {
            $date = today()->subDays($daysAgo);
            $rows = $attendanceCounts->get($date->toDateString(), collect());

            return [
                "label" => $date->format("D j"),
                "present" => (int) optional($rows->firstWhere("status", "present"))->total,
                "absent" => (int) optional($rows->firstWhere("status", "absent"))->total,
            ];
        })->values();

        // Students per class.
        $classDistribution = SchoolClass::withCount("students")
            ->orderBy("name")
            ->get()
            ->map(fn ($c) => ["label" => $c->name, "count" => $c->students_count])
            ->values();

        return view("admin.dashboard", compact("stats", "attendanceTrend", "classDistribution", "feeSummary"));
    }
}
