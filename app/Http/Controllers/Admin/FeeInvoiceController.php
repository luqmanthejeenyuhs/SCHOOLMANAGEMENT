<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class FeeInvoiceController extends Controller
{
    public function index()
    {
        $invoices = FeeInvoice::with(["student.user", "feeType", "payments"])->latest()->paginate(15);
        $students = Student::with("user")->get();
        $feeTypes = FeeType::all();
        $classes = SchoolClass::orderBy("name")->get();

        return view("admin.payments.index", compact("invoices", "students", "feeTypes", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "student_id" => "required|exists:students,id",
            "fee_type_id" => "required|exists:fee_types,id",
            "amount" => "required|numeric|min:0",
            "due_date" => "nullable|date",
        ]);
        $data["status"] = "unpaid";

        FeeInvoice::create($data);

        return back()->with("success", "Invoice generated.");
    }

    /**
     * Automated term billing: apply a fee type to every student (or every
     * student in one class) in a single click instead of one invoice at a
     * time. Skips anyone who already has an invoice for this fee type and
     * due date so re-running it is safe.
     */
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            "fee_type_id" => "required|exists:fee_types,id",
            "scope" => "required|in:all,class",
            "school_class_id" => "required_if:scope,class|nullable|exists:school_classes,id",
            "due_date" => "nullable|date",
        ]);

        $feeType = FeeType::findOrFail($data["fee_type_id"]);

        $students = Student::query()
            ->when($data["scope"] === "class", fn ($q) => $q->where("school_class_id", $data["school_class_id"]))
            ->get();

        $existingStudentIds = FeeInvoice::where("fee_type_id", $feeType->id)
            ->where("due_date", $data["due_date"] ?? null)
            ->pluck("student_id");

        $toInvoice = $students->whereNotIn("id", $existingStudentIds);

        $now = now();
        $rows = $toInvoice->map(fn ($student) => [
            "student_id" => $student->id,
            "fee_type_id" => $feeType->id,
            "amount" => $feeType->amount,
            "due_date" => $data["due_date"] ?? null,
            "status" => "unpaid",
            "created_at" => $now,
            "updated_at" => $now,
        ])->values();

        if ($rows->isNotEmpty()) {
            FeeInvoice::insert($rows->toArray());
        }

        $skipped = $students->count() - $rows->count();
        $message = "Generated {$rows->count()} invoice(s) for {$feeType->name}.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} student(s) who already had this invoice.";
        }

        return back()->with("success", $message);
    }

    public function recordPayment(Request $request, FeeInvoice $invoice)
    {
        $data = $request->validate([
            "amount_paid" => "required|numeric|min:0.01",
            "payment_date" => "required|date",
            "method" => "required|string|max:50",
        ]);
        $data["received_by"] = $request->user()->id;

        $invoice->payments()->create($data);

        $invoice->refresh();
        $balance = $invoice->balance();
        $invoice->update([
            "status" => $balance <= 0 ? "paid" : ($balance < $invoice->amount ? "partially_paid" : "unpaid"),
        ]);

        return back()->with("success", "Payment recorded.");
    }
}
