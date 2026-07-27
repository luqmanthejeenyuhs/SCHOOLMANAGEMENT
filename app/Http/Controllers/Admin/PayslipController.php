<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payslip;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function __construct(protected PayrollService $payroll)
    {
    }

    public function generate(Request $request, Employee $employee)
    {
        $data = $request->validate([
            "month" => "required|integer|min:1|max:12",
            "year" => "required|integer|min:2020|max:2100",
        ]);

        $allowances = $employee->house_allowance + $employee->transport_allowance + $employee->other_allowances;
        $breakdown = $this->payroll->calculate((float) $employee->basic_salary, (float) $allowances);

        // Unpaid leave = days explicitly marked "absent" for this employee within
        // the payroll month. "on_leave" is treated as approved/paid leave and is
        // NOT deducted — admins mark that status deliberately for sanctioned leave.
        $unpaidDays = $employee->staffAttendances()
            ->whereYear("date", $data["year"])
            ->whereMonth("date", $data["month"])
            ->where("status", "absent")
            ->count();

        $breakdown = $this->payroll->applyUnpaidLeaveDeduction($breakdown, $unpaidDays);

        $payslip = Payslip::updateOrCreate(
            ["employee_id" => $employee->id, "month" => $data["month"], "year" => $data["year"]],
            $breakdown
        );

        return redirect()->route("admin.payslips.show", $payslip)->with("success", "Payslip generated.");
    }

    public function show(Payslip $payslip)
    {
        $payslip->load("employee");

        return view("admin.payroll.payslip", compact("payslip"));
    }

    public function index()
    {
        $payslips = Payslip::with("employee")->latest()->paginate(15);

        return view("admin.payroll.payslips_index", compact("payslips"));
    }
}
