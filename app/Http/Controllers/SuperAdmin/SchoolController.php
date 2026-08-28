<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AccountProvisioningService;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Platform-level: managing the schools (tenants) themselves. Only reachable
 * by users with role => 'super_admin' (see the 'super_admin' route
 * middleware / App\Http\Middleware\EnsureSuperAdmin).
 */
class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::withCount(["users", "students", "teachers"])
            ->latest()
            ->get();

        return view("superadmin.schools.index", compact("schools"));
    }

    public function create()
    {
        return view("superadmin.schools.create");
    }

    public function store(Request $request, AccountProvisioningService $accounts)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "slug" => "required|alpha_dash|lowercase|unique:schools,slug",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "address" => "nullable|string",
            "admin_name" => "required|string|max:255",
            "admin_email" => ["required", "email", Rule::unique("users", "email")],
            "admin_username" => ["required", "string", "max:50", "alpha_dash"],
        ]);

        $school = School::create([
            "name" => $data["name"],
            "slug" => $data["slug"],
            "email" => $data["email"] ?? null,
            "phone" => $data["phone"] ?? null,
            "address" => $data["address"] ?? null,
        ]);

        // The school's first admin user is created "as" that school so it
        // gets school_id stamped automatically. Password is generated and
        // emailed, not chosen here — see AccountProvisioningService. The
        // platform super_admin creating this school never sees it either.
        Tenant::runFor($school->id, function () use ($data, $school, $accounts) {
            $accounts->createUserAccount([
                "name" => $data["admin_name"],
                "email" => $data["admin_email"],
                "username" => $data["admin_username"],
                "role" => "admin",
            ], $school);
        });

        return redirect()->route("superadmin.schools.index")->with("success", "School onboarded successfully.");
    }

    public function edit(School $school)
    {
        return view("superadmin.schools.edit", compact("school"));
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "slug" => ["required", "alpha_dash", "lowercase", Rule::unique("schools", "slug")->ignore($school->id)],
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "address" => "nullable|string",
        ]);

        $school->update($data);

        return back()->with("success", "School updated.");
    }

    /**
     * Suspend/reinstate a school. Deactivated schools' users are blocked at
     * login time (check role/is_active + school->is_active in your auth
     * controller — see README notes on wiring routes/auth).
     */
    public function toggleActive(School $school)
    {
        $school->update(["is_active" => ! $school->is_active]);

        return back()->with("success", $school->is_active ? "School reactivated." : "School suspended.");
    }

    public function destroy(School $school)
    {
        // Cascades: every tenant-scoped table has school_id
        // ->constrained()->cascadeOnDelete(), so this removes all of that
        // school's data too. There is no undo — confirm hard in the UI.
        $school->delete();

        return redirect()->route("superadmin.schools.index")->with("success", "School and all its data removed.");
    }
}
