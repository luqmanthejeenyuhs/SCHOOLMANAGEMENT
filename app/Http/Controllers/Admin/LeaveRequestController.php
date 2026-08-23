<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input("status", "pending");

        $requests = LeaveRequest::with(["employee", "reviewedBy"])
            ->when($status !== "all", fn ($q) => $q->where("status", $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view("admin.leave_requests.index", compact("requests", "status"));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            "status" => "approved",
            "reviewed_by" => $request->user()->id,
            "reviewed_at" => now(),
        ]);

        return back()->with("success", "Leave request approved.");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate(["review_note" => "nullable|string|max:255"]);

        $leaveRequest->update([
            "status" => "rejected",
            "reviewed_by" => $request->user()->id,
            "reviewed_at" => now(),
            "review_note" => $data["review_note"] ?? null,
        ]);

        return back()->with("success", "Leave request rejected.");
    }
}
