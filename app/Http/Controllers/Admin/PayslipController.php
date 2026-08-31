<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $payslip = DB::transaction(function () use ($data, $employee) {
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

            // Re-generating the same month (e.g. after fixing an attendance
            // record) must not pile up duplicate line items or double-deduct
            // a loan installment — wipe this payslip's prior itemization and
            // any loan repayment tied to it before recomputing from scratch.
            foreach ($payslip->items as $item) {
                $item->delete();
            }
            $existingLoanRepayment = \App\Models\LoanRepayment::where("payslip_id", $payslip->id)->first();
            if ($existingLoanRepayment) {
                $existingLoanRepayment->loan()->increment("balance_remaining", $existingLoanRepayment->amount);
                $existingLoanRepayment->loan()->update(["status" => "active"]);
                $existingLoanRepayment->delete();
            }

            $extraDeductions = 0;

            if ($unpaidDays > 0) {
                $dailyRate = $breakdown["basic_salary"] / max(1, config("payroll.working_days_per_month"));
                PayslipItem::create([
                    "payslip_id" => $payslip->id,
                    "label" => "Unpaid Leave ({$unpaidDays} day".($unpaidDays > 1 ? "s" : "").")",
                    "category" => "unpaid_leave",
                    "amount" => round($dailyRate * $unpaidDays, 2),
                ]);
            }

            // Custom deductions (SACCO, union dues, etc.) the employee is
            // actively assigned — see DeductionType/EmployeeDeduction.
            foreach ($employee->activeDeductions as $assigned) {
                $amount = $assigned->deductionType->is_percentage
                    ? round($breakdown["gross_pay"] * ($assigned->amount / 100), 2)
                    : (float) $assigned->amount;

                PayslipItem::create([
                    "payslip_id" => $payslip->id,
                    "label" => $assigned->deductionType->name,
                    "category" => "custom_deduction",
                    "amount" => $amount,
                ]);
                $extraDeductions += $amount;
            }

            // Loan/advance installment, if this employee has an active one.
            $loan = $employee->activeLoans()->first();
            if ($loan) {
                // Never deduct more than what's actually still owed — the
                // final installment is often smaller than the regular one.
                $loanDeduction = min((float) $loan->monthly_installment, (float) $loan->balance_remaining);

                if ($loanDeduction > 0) {
                    PayslipItem::create([
                        "payslip_id" => $payslip->id,
                        "label" => ucfirst($loan->loan_type)." Repayment",
                        "category" => "loan_repayment",
                        "amount" => $loanDeduction,
                    ]);
                    $extraDeductions += $loanDeduction;

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
            }

            if ($extraDeductions > 0) {
                $payslip->update([
                    "other_deductions" => round($payslip->other_deductions + $extraDeductions, 2),
                    "total_deductions" => round($payslip->total_deductions + $extraDeductions, 2),
                    "net_pay" => round($payslip->net_pay - $extraDeductions, 2),
                ]);
            }

            return $payslip;
        });

        return redirect()->route("admin.payslips.show", $payslip)->with("success", "Payslip generated.");
    }

    public function show(Payslip $payslip)
    {
        $payslip->load(["employee", "items"]);

        return view("admin.payroll.payslip", compact("payslip"));
    }

    public function index()
    {
        $payslips = Payslip::with("employee")->latest()->paginate(15);

        return view("admin.payroll.payslips_index", compact("payslips"));
    }
}
