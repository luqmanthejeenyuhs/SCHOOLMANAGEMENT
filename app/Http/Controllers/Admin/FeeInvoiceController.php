<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\PaymentAllocationService;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeInvoiceController extends Controller
{
    public function __construct(protected PaymentAllocationService $allocation)
    {
    }

    public function index()
    {
        $invoices = FeeInvoice::with(["student.user", "feeType", "payments"])->latest()->paginate(15);
        $students = Student::with("user")->get();
        $feeTypes = FeeType::all();
        $classes = SchoolClass::all();

        return view("admin.payments.index", compact("invoices", "students", "feeTypes", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "student_id" => ["required", Rule::exists("students", "id")->where("school_id", Tenant::id())],
            "fee_type_id" => ["required", Rule::exists("fee_types", "id")->where("school_id", Tenant::id())],
            "amount" => "required|numeric|min:0",
            "due_date" => "nullable|date",
        ]);
        $data["status"] = "unpaid";

        FeeInvoice::create($data);

        return back()->with("success", "Invoice generated.");
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            "fee_type_id" => ["required", Rule::exists("fee_types", "id")->where("school_id", Tenant::id())],
            "scope" => "required|in:all,class",
            "school_class_id" => [
                "nullable",
                "required_if:scope,class",
                Rule::exists("school_classes", "id")->where("school_id", Tenant::id()),
            ],
            "due_date" => "nullable|date",
        ]);

        $feeType = FeeType::findOrFail($data["fee_type_id"]);

        $students = Student::query()
            ->when($data["scope"] === "class", fn ($q) => $q->where("school_class_id", $data["school_class_id"]))
            ->get();

        $created = 0;

        foreach ($students as $student) {
            // "Already have this exact invoice" = same student + fee type + due date,
            // so re-running for a different term (different due date) is still safe.
            $invoice = FeeInvoice::firstOrNew([
                "student_id" => $student->id,
                "fee_type_id" => $feeType->id,
                "due_date" => $data["due_date"] ?? null,
            ]);

            if (! $invoice->exists) {
                $invoice->amount = $feeType->amount;
                $invoice->status = "unpaid";
                $invoice->save();
                $created++;
            }
        }

        $skipped = $students->count() - $created;
        $message = "Generated {$created} invoice(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} student(s) already had this invoice and were skipped.";
        }

        return back()->with("success", $message);
    }

    public function recordPayment(Request $request, FeeInvoice $invoice)
    {
        $data = $request->validate([
            "amount_paid" => "required|numeric|min:0.01",
            "payment_date" => "required|date",
            "method" => "required|string|max:50",
            "bank_name" => "nullable|required_if:method,bank|string|max:100",
            "reference" => "nullable|string|max:100",
        ]);

        $amountPaid = (float) $data["amount_paid"];
        unset($data["amount_paid"]);
        $data["received_by"] = $request->user()->id;

        // Caps the payment at this invoice's balance; any excess is applied
        // automatically to the student's next outstanding invoice(s), and
        // if none remain, to this invoice as a visible credit. May create
        // more than one Payment row (and so more than one receipt) if the
        // amount was split across invoices — see PaymentAllocationService.
        $payments = $this->allocation->apply($invoice, $amountPaid, $data);

        $receiptUrls = collect($payments)
            ->map(fn ($payment) => $payment->receipt ? route("admin.receipts.show", $payment->receipt) : null)
            ->filter()
            ->values()
            ->all();

        $message = count($payments) > 1
            ? "Payment recorded across ".count($payments)." invoices (the excess was applied to the next outstanding fee)."
            : "Payment recorded.";

        return back()->with("success", $message)->with("receipt_urls", $receiptUrls);
    }
}
