<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformInvoice;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query("status");
        $schoolId = $request->query("school_id");

        $invoices = PlatformInvoice::with("school")
            ->when($status, fn ($q) => $q->where("status", $status))
            ->when($schoolId, fn ($q) => $q->where("school_id", $schoolId))
            ->latest("due_date")
            ->paginate(20)
            ->withQueryString();

        // Flip anything now overdue while we're here, rather than needing
        // a scheduled job just to keep the status column honest.
        PlatformInvoice::where("status", "pending")->where("due_date", "<", now())->update(["status" => "overdue"]);

        $schools = School::orderBy("name")->get(["id", "name"]);

        $summary = [
            "outstanding" => (float) PlatformInvoice::whereIn("status", ["pending", "overdue"])->sum("amount"),
            "overdue" => (float) PlatformInvoice::where("status", "overdue")->sum("amount"),
            "collected" => (float) PlatformInvoice::where("status", "paid")->sum("amount"),
        ];

        return view("superadmin.billing.index", compact("invoices", "schools", "status", "schoolId", "summary"));
    }

    public function create()
    {
        $schools = School::orderBy("name")->get(["id", "name", "plan"]);

        return view("superadmin.billing.create", compact("schools"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "school_id" => "required|exists:schools,id",
            "amount" => "required|numeric|min:0",
            "billing_cycle" => "required|in:monthly,termly,annual,one_off",
            "due_date" => "required|date",
            "note" => "nullable|string|max:500",
        ]);

        $data["created_by"] = Auth::id();

        PlatformInvoice::create($data);

        return redirect()->route("superadmin.billing.index")->with("success", "Invoice created.");
    }

    public function markPaid(Request $request, PlatformInvoice $invoice)
    {
        $data = $request->validate([
            "paid_method" => "required|in:bank_transfer,mpesa,cash,cheque",
        ]);

        $invoice->update([
            "status" => "paid",
            "paid_at" => now(),
            "paid_method" => $data["paid_method"],
        ]);

        return back()->with("success", "Marked as paid.");
    }

    public function destroy(PlatformInvoice $invoice)
    {
        $invoice->delete();

        return back()->with("success", "Invoice removed.");
    }
}
