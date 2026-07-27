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

        return view("admin.dashboard", compact("stats"));
    }
}
