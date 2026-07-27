<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::latest()->paginate(15);

        return view("admin.payroll.employees.index", compact("employees"));
    }

    public function create()
    {
        // Only offer users not already linked to a payroll record — keeps the
        // dropdown short and prevents a staff member from double-clocking under
        // two employee rows.
        $linkedUserIds = Employee::whereNotNull("user_id")->pluck("user_id");
        $users = User::whereNotIn("role", ["student"])->whereNotIn("id", $linkedUserIds)->orderBy("name")->get();

        return view("admin.payroll.employees.create", compact("users"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "job_title" => "required|string|max:255",
            "is_teaching_staff" => "nullable|boolean",
            "user_id" => "nullable|exists:users,id",
            "id_number" => "nullable|string|max:50",
            "kra_pin" => "nullable|string|max:50",
            "nssf_number" => "nullable|string|max:50",
            "shif_number" => "nullable|string|max:50",
            "phone" => "nullable|string|max:50",
            "basic_salary" => "required|numeric|min:0",
            "house_allowance" => "nullable|numeric|min:0",
            "transport_allowance" => "nullable|numeric|min:0",
            "other_allowances" => "nullable|numeric|min:0",
            "employment_date" => "nullable|date",
        ]);
        $data["is_teaching_staff"] = $request->boolean("is_teaching_staff");

        Employee::create($data);

        return redirect()->route("admin.employees.index")->with("success", "Employee added to payroll.");
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with("success", "Employee removed.");
    }
}
