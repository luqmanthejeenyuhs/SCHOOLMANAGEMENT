<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;

class PayslipController extends Controller
{
    public function index()
    {
        $employee = Employee::where("user_id", auth()->id())->first();

        $payslips = $employee
            ? $employee->payslips()->orderByDesc("year")->orderByDesc("month")->get()
            : collect();

        return view("teacher.payslips.index", compact("employee", "payslips"));
    }
}
