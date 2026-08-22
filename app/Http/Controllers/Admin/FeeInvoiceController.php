<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeInvoiceController extends Controller
{
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
        $data["received_by"] = $request->user()->id;

        $payment = $invoice->payments()->create($data);

        $invoice->refresh();
        $balance = $invoice->balance();
        $invoice->update([
            "status" => $balance <= 0 ? "paid" : ($balance < $invoice->amount ? "partially_paid" : "unpaid"),
        ]);

        // PaymentObserver::created() runs synchronously during the create()
        // call above, so the receipt already exists by the time we get here.
        $receiptUrl = $payment->receipt ? route("admin.receipts.show", $payment->receipt) : null;

        return back()->with("success", "Payment recorded.")->with("receipt_url", $receiptUrl);
    }
}
