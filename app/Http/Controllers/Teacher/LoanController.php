<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LoanRequest;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index()
    {
        $employee = Employee::where("user_id", auth()->id())->first();

        $requests = $employee
            ? LoanRequest::where("employee_id", $employee->id)->latest()->get()
            : collect();

        return view("teacher.loans.index", compact("employee", "requests"));
    }

    public function store(Request $request)
    {
        $employee = Employee::where("user_id", auth()->id())->first();

        abort_unless($employee, 404, "No staff record is linked to your account yet. Ask an admin to link it.");

        $data = $request->validate([
            "request_type" => "required|in:".implode(",", array_keys(LoanRequest::TYPES)),
            "amount" => "required|numeric|min:1",
            "reason" => "nullable|string|max:1000",
        ]);

        LoanRequest::create([
            "employee_id" => $employee->id,
            "request_type" => $data["request_type"],
            "amount" => $data["amount"],
            "reason" => $data["reason"] ?? null,
            "status" => "pending",
        ]);

        return back()->with("success", "Request submitted — you'll be notified once it's reviewed.");
    }
}
