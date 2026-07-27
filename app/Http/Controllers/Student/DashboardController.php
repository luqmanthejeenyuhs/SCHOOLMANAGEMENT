<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student()->with([
            "schoolClass", "section",
            "attendances" => fn ($q) => $q->latest("date")->limit(10),
            "examResults.exam", "examResults.subject",
            "feeInvoices.payments", "feeInvoices.feeType",
        ])->first();

        $attendanceSummary = [
            "present" => $student ? $student->attendances()->where("status", "present")->count() : 0,
            "absent" => $student ? $student->attendances()->where("status", "absent")->count() : 0,
            "late" => $student ? $student->attendances()->where("status", "late")->count() : 0,
        ];

        return view("student.dashboard", compact("student", "attendanceSummary"));
    }
}
