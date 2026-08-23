<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = Employee::where("user_id", auth()->id())->first();

        $requests = $employee
            ? LeaveRequest::where("employee_id", $employee->id)->latest()->get()
            : collect();

        return view("teacher.leave.index", compact("employee", "requests"));
    }

    public function store(Request $request)
    {
        $employee = Employee::where("user_id", auth()->id())->first();

        abort_unless($employee, 404, "No staff record is linked to your account yet. Ask an admin to link it.");

        $data = $request->validate([
            "leave_type" => "required|in:".implode(",", array_keys(LeaveRequest::TYPES)),
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "reason" => "nullable|string|max:1000",
        ]);

        $days = \Carbon\Carbon::parse($data["start_date"])->diffInDays(\Carbon\Carbon::parse($data["end_date"])) + 1;

        LeaveRequest::create([
            "employee_id" => $employee->id,
            "leave_type" => $data["leave_type"],
            "start_date" => $data["start_date"],
            "end_date" => $data["end_date"],
            "days" => $days,
            "reason" => $data["reason"] ?? null,
            "status" => "pending",
        ]);

        return back()->with("success", "Leave request submitted — you'll be notified once it's reviewed.");
    }
}
