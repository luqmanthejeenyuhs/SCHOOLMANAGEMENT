<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            "user_id" => ["nullable", Rule::exists("users", "id")->where("school_id", Tenant::id())],
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

    /**
     * The only way to link (or fix a wrongly-linked) user_id after an employee
     * has already been created — without this, a staff member whose account
     * wasn't linked at creation time has no way to ever clock in, since there
     * was previously no edit screen at all.
     */
    public function edit(Employee $employee)
    {
        // Exclude users already linked to a *different* employee, but keep this
        // employee's own current user (if any) selectable/shown.
        $linkedUserIds = Employee::whereNotNull("user_id")->where("id", "!=", $employee->id)->pluck("user_id");
        $users = User::whereNotIn("role", ["student"])->whereNotIn("id", $linkedUserIds)->orderBy("name")->get();

        return view("admin.payroll.employees.edit", compact("employee", "users"));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "job_title" => "required|string|max:255",
            "is_teaching_staff" => "nullable|boolean",
            "user_id" => [
                "nullable",
                Rule::exists("users", "id")->where("school_id", Tenant::id()),
                Rule::unique("employees", "user_id")->ignore($employee->id),
            ],
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

        $employee->update($data);

        return redirect()->route("admin.employees.index")->with("success", "Employee updated.");
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with("success", "Employee removed.");
    }
}
