<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use Illuminate\Http\Request;

class DeductionTypeController extends Controller
{
    public function index()
    {
        $statutory = DeductionType::where("is_statutory", true)->orderBy("name")->get();
        $custom = DeductionType::where("is_statutory", false)->withCount("employeeDeductions")->orderBy("name")->get();

        return view("admin.payroll.deduction_types.index", compact("statutory", "custom"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "is_percentage" => "nullable|boolean",
            "rate_or_amount" => "required|numeric|min:0",
        ]);
        $data["is_percentage"] = $request->boolean("is_percentage");
        $data["is_statutory"] = false;

        DeductionType::create($data);

        return back()->with("success", "Deduction type added.");
    }

    public function update(Request $request, DeductionType $deductionType)
    {
        abort_if($deductionType->is_statutory, 403, "Statutory items are computed automatically and can't be edited here.");

        $data = $request->validate([
            "name" => "required|string|max:255",
            "is_percentage" => "nullable|boolean",
            "rate_or_amount" => "required|numeric|min:0",
            "is_active" => "nullable|boolean",
        ]);
        $data["is_percentage"] = $request->boolean("is_percentage");
        $data["is_active"] = $request->boolean("is_active");

        $deductionType->update($data);

        return back()->with("success", "Deduction type updated.");
    }

    public function destroy(DeductionType $deductionType)
    {
        abort_if($deductionType->is_statutory, 403, "Statutory items can't be deleted.");

        $deductionType->delete();

        return back()->with("success", "Deduction type removed.");
    }

    /**
     * Assigning a custom deduction type to a specific employee, with an
     * amount that may differ per employee (e.g. two staff on different
     * SACCO contribution amounts).
     */
    public function assign(Request $request)
    {
        $data = $request->validate([
            "employee_id" => "required|exists:employees,id",
            "deduction_type_id" => ["required", "exists:deduction_types,id"],
            "amount" => "required|numeric|min:0",
        ]);

        $type = DeductionType::findOrFail($data["deduction_type_id"]);
        abort_if($type->is_statutory, 422, "Statutory deductions apply automatically and can't be assigned manually.");

        EmployeeDeduction::updateOrCreate(
            ["employee_id" => $data["employee_id"], "deduction_type_id" => $data["deduction_type_id"]],
            ["amount" => $data["amount"], "is_active" => true]
        );

        return back()->with("success", "Deduction assigned to employee.");
    }

    public function unassign(EmployeeDeduction $employeeDeduction)
    {
        $employeeDeduction->delete();

        return back()->with("success", "Deduction removed from employee.");
    }
}
