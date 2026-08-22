<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCredit;
use App\Services\AccountingService;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeInvoiceController extends Controller
{
    public function __construct(protected AccountingService $accounting)
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

        $invoice = FeeInvoice::create($data);
        $this->applyAvailableCredit($invoice, $request->user()->id);

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
                $this->applyAvailableCredit($invoice, $request->user()->id);
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
        $userId = $request->user()->id;
        $studentLabel = optional($invoice->student)->admission_no;

        $remaining = round((float) $data["amount_paid"], 2);

        // 1. Apply to the invoice actually being paid, capped at its own
        // balance — so its own payment history and status stay clean and
        // it never shows as "overpaid" itself.
        $appliedHere = min($remaining, $invoice->balance());
        if ($appliedHere > 0) {
            $invoice->payments()->create(array_merge($data, [
                "amount_paid" => $appliedHere,
                "received_by" => $userId,
            ]));
            $remaining = round($remaining - $appliedHere, 2);
        }

        $this->refreshInvoiceStatus($invoice->fresh());

        // 2. Cascade any excess to the student's other outstanding
        // invoices, oldest due date first — this is what lets an
        // overpayment on one fee type (e.g. Tuition) settle another
        // (e.g. Transport) automatically.
        $appliedToOthers = [];

        if ($remaining > 0 && $invoice->student_id) {
            $otherInvoices = FeeInvoice::where("student_id", $invoice->student_id)
                ->where("id", "!=", $invoice->id)
                ->where("status", "!=", "paid")
                ->orderBy("due_date")
                ->get();

            foreach ($otherInvoices as $other) {
                if ($remaining <= 0) {
                    break;
                }

                $applyAmount = min($remaining, $other->balance());
                if ($applyAmount <= 0) {
                    continue;
                }

                $other->payments()->create(array_merge($data, [
                    "amount_paid" => $applyAmount,
                    "received_by" => $userId,
                ]));
                $this->refreshInvoiceStatus($other->fresh());

                $appliedToOthers[] = optional($other->feeType)->name." (KES ".number_format($applyAmount, 2).")";
                $remaining = round($remaining - $applyAmount, 2);
            }
        }

        // 3. Still something left over — the student has no other
        // outstanding invoice to apply it to right now. Hold it as a real
        // liability (not revenue yet) rather than losing track of it.
        $heldAsCredit = 0.0;

        if ($remaining > 0 && $invoice->student_id) {
            $this->accounting->holdAsCredit(
                schoolId: $invoice->school_id,
                studentId: $invoice->student_id,
                amount: $remaining,
                method: $data["method"],
                bankName: $data["bank_name"] ?? null,
                reference: $data["reference"] ?? null,
                userId: $userId,
                studentLabel: $studentLabel,
            );
            $heldAsCredit = $remaining;
        }

        return back()->with("success", $this->paymentSummaryMessage($appliedHere, $appliedToOthers, $heldAsCredit));
    }

    protected function refreshInvoiceStatus(FeeInvoice $invoice): void
    {
        $balance = $invoice->balance();
        $invoice->update([
            "status" => $balance <= 0 ? "paid" : ($balance < $invoice->amount ? "partially_paid" : "unpaid"),
        ]);
    }

    protected function paymentSummaryMessage(float $appliedHere, array $appliedToOthers, float $heldAsCredit): string
    {
        $message = "Payment recorded: KES ".number_format($appliedHere, 2)." applied to this invoice.";

        if (! empty($appliedToOthers)) {
            $message .= " Excess automatically applied to: ".implode(", ", $appliedToOthers).".";
        }

        if ($heldAsCredit > 0) {
            $message .= " KES ".number_format($heldAsCredit, 2)." held as student credit — no other outstanding ".
                "invoices right now, so it'll be applied automatically the next time one is created for this student.";
        }

        return $message;
    }

    /**
     * Auto-applies any pre-existing student credit (see
     * AccountingService::holdAsCredit) the moment a new invoice for that
     * student appears — called from both store() and bulkStore(). Posts as
     * a normal Payment with method="credit_balance", which
     * PaymentObserver/AccountingService already know how to post correctly
     * (debits the liability, credits Fees Income, no new cash involved).
     */
    protected function applyAvailableCredit(FeeInvoice $invoice, ?int $userId): void
    {
        if (! $invoice->student_id) {
            return;
        }

        $credit = StudentCredit::where("student_id", $invoice->student_id)->first();
        if (! $credit || $credit->balance <= 0) {
            return;
        }

        $applyAmount = min((float) $credit->balance, $invoice->balance());
        if ($applyAmount <= 0) {
            return;
        }

        $invoice->payments()->create([
            "amount_paid" => $applyAmount,
            "payment_date" => now()->toDateString(),
            "method" => "credit_balance",
            "received_by" => $userId,
        ]);

        $this->refreshInvoiceStatus($invoice->fresh());
    }
}
