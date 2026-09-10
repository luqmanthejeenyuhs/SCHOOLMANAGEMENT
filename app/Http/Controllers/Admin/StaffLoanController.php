<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LoanRepayment;
use App\Models\StaffLoan;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffLoanController extends Controller
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function index(Request $request)
    {
        $loans = StaffLoan::with("employee")
            ->when($request->get("status"), fn ($q, $status) => $q->where("status", $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view("admin.loans.index", compact("loans"));
    }

    public function create()
    {
        $employees = Employee::orderBy("name")->get();

        return view("admin.loans.create", compact("employees"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "employee_id" => "required|exists:employees,id",
            "loan_type" => "required|in:loan,advance",
            "interest_method" => "required|in:simple,compound,flat",
            "principal" => "required|numeric|min:1",
            "interest_rate" => "required|numeric|min:0|max:100",
            "repayment_period_months" => "required|integer|min:1|max:60",
            "start_date" => "required|date",
            "disbursement_method" => "required|in:cash,bank,mpesa",
        ]);

        // Refuse a second concurrent active loan for the same employee —
        // keeps payroll deduction logic simple (one installment per payslip)
        // and avoids a staff member quietly stacking debt against payroll.
        $hasActive = StaffLoan::where("employee_id", $data["employee_id"])->where("status", "active")->exists();
        if ($hasActive) {
            return back()->withErrors(["employee_id" => "This employee already has an active loan/advance. It must be completed or written off before a new one can be issued."])->withInput();
        }

        $terms = StaffLoan::calculateTerms($data["principal"], $data["interest_rate"], $data["repayment_period_months"], $data["interest_method"]);

        $loan = DB::transaction(function () use ($data, $terms, $request) {
            $loan = StaffLoan::create([
                "employee_id" => $data["employee_id"],
                "loan_type" => $data["loan_type"],
                "interest_method" => $data["interest_method"],
                "principal" => $data["principal"],
                "interest_rate" => $data["interest_rate"],
                "repayment_period_months" => $data["repayment_period_months"],
                "total_interest" => $terms["total_interest"],
                "total_repayable" => $terms["total_repayable"],
                "monthly_installment" => $terms["monthly_installment"],
                "balance_remaining" => $terms["total_repayable"],
                "start_date" => $data["start_date"],
                "approved_by" => $request->user()->id,
            ]);

            $this->accounting->postLoanDisbursement($loan, $data["disbursement_method"]);

            return $loan;
        });

        return redirect()->route("admin.loans.show", $loan)->with("success", "Loan disbursed and will be deducted automatically from future payslips.");
    }

    public function show(StaffLoan $loan)
    {
        $loan->load(["employee", "repayments" => fn ($q) => $q->latest("payment_date")]);

        return view("admin.loans.show", compact("loan"));
    }

    public function recordManualRepayment(Request $request, StaffLoan $loan)
    {
        $data = $request->validate([
            "amount" => ["required", "numeric", "min:0.01", "lte:".$loan->balance_remaining],
            "payment_date" => "required|date",
            "method" => "required|in:cash,bank,mpesa",
        ]);

        DB::transaction(function () use ($data, $loan) {
            $split = $loan->splitRepayment($data["amount"]);

            $repayment = LoanRepayment::create([
                "staff_loan_id" => $loan->id,
                "payslip_id" => null,
                "amount" => $data["amount"],
                "principal_portion" => $split["principal_portion"],
                "interest_portion" => $split["interest_portion"],
                "payment_date" => $data["payment_date"],
            ]);

            $this->accounting->postManualLoanRepayment($repayment, $data["method"]);

            $loan->decrement("balance_remaining", $data["amount"]);
            $loan->refresh();
            if ($loan->balance_remaining <= 0.01) {
                $loan->update(["status" => "completed", "balance_remaining" => 0]);
            }
        });

        return back()->with("success", "Repayment recorded.");
    }

    public function writeOff(StaffLoan $loan)
    {
        $loan->update(["status" => "written_off"]);

        return back()->with("success", "Loan marked as written off.");
    }
}
