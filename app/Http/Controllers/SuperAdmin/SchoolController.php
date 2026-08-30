<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SchoolAdminCredentials;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
            ->withMax("users as last_login_at", "last_login_at")
            ->latest()
            ->get();

        // ->allSchools() bypasses tenant scoping deliberately here — this is
        // the one place in the app a cross-school aggregate is correct.
        $stats = [
            "total_schools" => $schools->count(),
            "active_schools" => $schools->where("is_active", true)->count(),
            "total_students" => Student::allSchools()->count(),
            "total_revenue" => Payment::allSchools()->sum("amount_paid"),
        ];

        return view("superadmin.schools.index", compact("schools", "stats"));
    }

    public function create()
    {
        return view("superadmin.schools.create");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "slug" => "required|alpha_dash|lowercase|unique:schools,slug",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "address" => "nullable|string",
            "plan" => "required|in:trial,basic,premium",
            "admin_name" => "required|string|max:255",
            "admin_email" => ["required", "email", Rule::unique("users", "email")],
        ]);

        $school = School::create([
            "name" => $data["name"],
            "slug" => $data["slug"],
            "plan" => $data["plan"],
            "email" => $data["email"] ?? null,
            "phone" => $data["phone"] ?? null,
            "address" => $data["address"] ?? null,
        ]);

        // Nobody types this in, and nobody on our side ever sees it — it's
        // generated here, emailed directly to the school's admin, and never
        // stored or logged anywhere in plaintext. They set their own real
        // password on first login (see EnsurePasswordIsChanged).
        $temporaryPassword = Str::password(14);

        // The school's first admin user is created "as" that school so it
        // gets school_id stamped automatically.
        $admin = Tenant::runFor($school->id, function () use ($data, $temporaryPassword) {
            return User::create([
                "name" => $data["admin_name"],
                "email" => $data["admin_email"],
                "password" => Hash::make($temporaryPassword),
                "role" => "admin",
                "must_change_password" => true,
            ]);
        });

        $loginUrl = $this->loginUrlFor($school);

        Mail::to($admin->email)->send(new SchoolAdminCredentials($admin, $school, $temporaryPassword, $loginUrl));

        return redirect()->route("superadmin.schools.index")
            ->with("success", "School onboarded. Login details were emailed to {$admin->email}.");
    }

    protected function loginUrlFor(School $school): string
    {
        $platformDomain = config("school.platform_domain");

        if (! $platformDomain) {
            return url("/login");
        }

        return "https://{$school->slug}.{$platformDomain}/login";
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
            "plan" => "required|in:trial,basic,premium",
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
