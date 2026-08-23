<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use Illuminate\Http\Request;

class LoanRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input("status", "pending");

        $requests = LoanRequest::with(["employee", "reviewedBy"])
            ->when($status !== "all", fn ($q) => $q->where("status", $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view("admin.loan_requests.index", compact("requests", "status"));
    }

    public function approve(Request $request, LoanRequest $loanRequest)
    {
        $loanRequest->update([
            "status" => "approved",
            "reviewed_by" => $request->user()->id,
            "reviewed_at" => now(),
        ]);

        return back()->with("success", "Request approved.");
    }

    public function reject(Request $request, LoanRequest $loanRequest)
    {
        $data = $request->validate(["review_note" => "nullable|string|max:255"]);

        $loanRequest->update([
            "status" => "rejected",
            "reviewed_by" => $request->user()->id,
            "reviewed_at" => now(),
            "review_note" => $data["review_note"] ?? null,
        ]);

        return back()->with("success", "Request rejected.");
    }

    /**
     * A separate step from approve() on purpose — this is for once the
     * money has actually gone out, not just been decided on.
     */
    public function markDisbursed(LoanRequest $loanRequest)
    {
        if ($loanRequest->status !== "approved") {
            return back()->with("error", "Only an approved request can be marked as disbursed.");
        }

        $loanRequest->update(["status" => "disbursed", "disbursed_at" => now()]);

        return back()->with("success", "Marked as disbursed.");
    }
}
