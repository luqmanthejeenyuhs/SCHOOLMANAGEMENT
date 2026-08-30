<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\SupplierBillPayment;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierBillController extends Controller
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function index(Request $request)
    {
        $bills = SupplierBill::with("supplier")
            ->when($request->get("status"), fn ($q, $status) => $q->where("status", $status))
            ->when($request->get("supplier_id"), fn ($q, $id) => $q->where("supplier_id", $id))
            ->latest("bill_date")
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy("name")->get();

        return view("admin.suppliers.bills.index", compact("bills", "suppliers"));
    }

    public function create()
    {
        $suppliers = Supplier::where("is_active", true)->orderBy("name")->get();
        $vatRate = config("vat.standard_rate");

        return view("admin.suppliers.bills.create", compact("suppliers", "vatRate"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "supplier_id" => "required|exists:suppliers,id",
            "bill_reference" => "nullable|string|max:100",
            "description" => "required|string|max:255",
            "amount" => "required|numeric|min:0.01",
            "is_vatable" => "nullable|boolean",
            "bill_date" => "required|date",
        ]);

        $supplier = Supplier::findOrFail($data["supplier_id"]);
        $vatRate = $request->boolean("is_vatable") ? (float) config("vat.standard_rate") : 0;
        $vatAmount = round($data["amount"] * ($vatRate / 100), 2);
        $totalAmount = round($data["amount"] + $vatAmount, 2);

        $bill = DB::transaction(function () use ($data, $supplier, $vatRate, $vatAmount, $totalAmount, $request) {
            $bill = SupplierBill::create([
                "supplier_id" => $data["supplier_id"],
                "bill_reference" => $data["bill_reference"] ?? null,
                "description" => $data["description"],
                "amount" => $data["amount"],
                "vat_rate" => $vatRate,
                "vat_amount" => $vatAmount,
                "total_amount" => $totalAmount,
                "bill_date" => $data["bill_date"],
                "due_date" => \Carbon\Carbon::parse($data["bill_date"])->addDays($supplier->payment_terms_days),
                "created_by" => $request->user()->id,
            ]);

            $this->accounting->postSupplierBill($bill);

            return $bill;
        });

        return redirect()->route("admin.suppliers.bills.show", $bill)->with("success", "Bill recorded.");
    }

    public function show(SupplierBill $bill)
    {
        $bill->load(["supplier", "payments", "creditNotes"]);

        return view("admin.suppliers.bills.show", compact("bill"));
    }

    public function recordPayment(Request $request, SupplierBill $bill)
    {
        $data = $request->validate([
            "amount" => ["required", "numeric", "min:0.01", "lte:".$bill->balance()],
            "payment_date" => "required|date",
            "method" => "required|in:cash,bank,mpesa,card",
            "reference" => "nullable|string|max:100",
        ]);

        DB::transaction(function () use ($data, $bill, $request) {
            $payment = SupplierBillPayment::create([
                "supplier_bill_id" => $bill->id,
                "amount" => $data["amount"],
                "payment_date" => $data["payment_date"],
                "method" => $data["method"],
                "reference" => $data["reference"] ?? null,
                "paid_by" => $request->user()->id,
            ]);

            $this->accounting->postSupplierBillPayment($payment);

            $bill->refresh();
            $bill->update([
                "status" => $bill->balance() <= 0 ? "paid" : "partially_paid",
            ]);
        });

        return back()->with("success", "Payment recorded.");
    }

    public function storeCreditNote(Request $request, SupplierBill $bill)
    {
        $data = $request->validate([
            "amount" => ["required", "numeric", "min:0.01", "lte:".$bill->balance()],
            "reason" => "required|string|max:255",
            "date" => "required|date",
        ]);

        DB::transaction(function () use ($data, $bill, $request) {
            $creditNote = CreditNote::create([
                "supplier_id" => $bill->supplier_id,
                "supplier_bill_id" => $bill->id,
                "amount" => $data["amount"],
                "reason" => $data["reason"],
                "date" => $data["date"],
                "issued_by" => $request->user()->id,
            ]);

            $this->accounting->postCreditNote($creditNote);

            $bill->refresh();
            $bill->update([
                "status" => $bill->balance() <= 0 ? "paid" : "partially_paid",
            ]);
        });

        return back()->with("success", "Credit note issued.");
    }
}
