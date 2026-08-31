<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = LeaveRequest::with("employee")
            ->when($request->get("status"), fn ($q, $status) => $q->where("status", $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $employees = Employee::orderBy("name")->get();

        return view("admin.payroll.leave_requests.index", compact("requests", "employees"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "employee_id" => "required|exists:employees,id",
            "leave_type" => "required|in:annual,sick,unpaid,maternity,paternity,other",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "reason" => "nullable|string|max:500",
        ]);

        LeaveRequest::create($data);

        return back()->with("success", "Leave request recorded.");
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate(["status" => "required|in:approved,rejected"]);

        $leaveRequest->update([
            "status" => $data["status"],
            "decided_by" => $request->user()->id,
        ]);

        return back()->with("success", "Leave request ".$data["status"].".");
    }
}
