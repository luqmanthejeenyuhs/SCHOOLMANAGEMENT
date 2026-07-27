<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $parent = Auth::user();

        $children = $parent->children()
            ->with(["user", "schoolClass", "section"])
            ->get()
            ->map(function ($student) {
                $invoices = $student->feeInvoices;
                $student->fee_balance_total = $invoices->sum(fn ($inv) => $inv->balance());

                $recentAttendance = $student->attendances()
                    ->where("date", ">=", now()->subDays(30))
                    ->get();
                $total = $recentAttendance->count();
                $present = $recentAttendance->whereIn("status", ["present", "late"])->count();
                $student->attendance_rate = $total > 0 ? round(($present / $total) * 100, 1) : null;

                $results = $student->examResults;
                $student->exam_average = $results->count()
                    ? round($results->avg(fn ($r) => $r->percentage()), 1)
                    : null;

                return $student;
            });

        return view("parent.dashboard", compact("children"));
    }

    public function show($studentId)
    {
        $parent = Auth::user();

        // findOrFail scoped through the parent's own children relation —
        // a parent can never view a student that isn't linked to them.
        $student = $parent->children()->with(["user", "schoolClass", "section"])->findOrFail($studentId);

        $invoices = $student->feeInvoices()->with("payments")->latest()->get();
        $feeBalance = $invoices->sum(fn ($inv) => $inv->balance());

        $examResults = $student->examResults()
            ->with(["exam", "subject"])
            ->get()
            ->groupBy(fn ($r) => $r->exam->name ?? "Exam #".$r->exam_id);

        $attendance = $student->attendances()
            ->where("date", ">=", now()->subDays(60))
            ->orderByDesc("date")
            ->get();

        return view("parent.child", compact("student", "invoices", "feeBalance", "examResults", "attendance"));
    }
}
