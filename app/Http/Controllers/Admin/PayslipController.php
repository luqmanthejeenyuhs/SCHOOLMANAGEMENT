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

        $loan = $employee->activeLoans()->first();
        $loanDeduction = 0;

        if ($loan) {
            // Never deduct more than what's actually still owed — the final
            // installment on a loan is often smaller than the regular one.
            $loanDeduction = min((float) $loan->monthly_installment, (float) $loan->balance_remaining);
            $breakdown["other_deductions"] = ($breakdown["other_deductions"] ?? 0) + $loanDeduction;
            $breakdown["total_deductions"] += $loanDeduction;
            $breakdown["net_pay"] -= $loanDeduction;
        }

        $payslip = Payslip::updateOrCreate(
            ["employee_id" => $employee->id, "month" => $data["month"], "year" => $data["year"]],
            $breakdown
        );

        if ($loan && $loanDeduction > 0) {
            // Reverse any deduction already recorded against this exact
            // payslip before re-adding — updateOrCreate above means
            // "Generate" can be clicked again for the same month (e.g. after
            // fixing an attendance record), and without this a re-generate
            // would double-count the loan deduction against the balance.
            $existing = $loan->repayments()->where("payslip_id", $payslip->id)->first();
            if ($existing) {
                $loan->increment("balance_remaining", $existing->amount);
                $existing->delete();
            }

            $split = $loan->splitRepayment($loanDeduction);
            $loan->repayments()->create([
                "payslip_id" => $payslip->id,
                "amount" => $loanDeduction,
                "principal_portion" => $split["principal_portion"],
                "interest_portion" => $split["interest_portion"],
                "payment_date" => now()->toDateString(),
            ]);

            $loan->decrement("balance_remaining", $loanDeduction);
            $loan->refresh();
            if ($loan->balance_remaining <= 0.01) {
                $loan->update(["status" => "completed", "balance_remaining" => 0]);
            }
        }

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
