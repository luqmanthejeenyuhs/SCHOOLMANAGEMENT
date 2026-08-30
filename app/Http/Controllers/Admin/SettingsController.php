<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        return view("admin.settings.index");
    }

    public function schoolProfile()
    {
        $school = Tenant::current();

        return view("admin.settings.school_profile", compact("school"));
    }

    public function schoolProfileUpdate(Request $request)
    {
        $school = Tenant::current();

        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "nullable|email|max:255",
            "phone" => "nullable|string|max:50",
            "address" => "nullable|string|max:500",
            "logo" => "nullable|image|max:2048",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",
            "geofence_radius_meters" => "nullable|integer|min:20|max:5000",
            "expected_clock_in" => "required|date_format:H:i",
            "expected_clock_out" => "required|date_format:H:i",
        ]);

        if ($request->hasFile("logo")) {
            if ($school->logo_path) {
                \Illuminate\Support\Facades\Storage::disk("public")->delete($school->logo_path);
            }

            $data["logo_path"] = $request->file("logo")->store("school-logos", "public");
        }

        unset($data["logo"]);

        $school->update($data);

        return back()->with("success", "School profile updated.");
    }

    public function rightsIndex(Request $request)
    {
        $search = trim((string) $request->query("q", ""));

        $users = User::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%");
            }))
            ->withCount("permissions")
            ->orderBy("name")
            ->paginate(15)
            ->withQueryString();

        return view("admin.settings.rights.index", compact("users", "search"));
    }

    public function rightsEdit(User $user)
    {
        $categories = Permission::orderBy("category")->orderBy("name")->get()->groupBy("category");
        $userPermissionKeys = $user->permissions()->pluck("key")->all();

        return view("admin.settings.rights.edit", compact("user", "categories", "userPermissionKeys"));
    }

    public function rightsUpdate(Request $request, User $user)
    {
        if ($user->is_super_admin) {
            return back()->with("error", "Super admins already have every right — nothing to assign.");
        }

        $data = $request->validate([
            "permissions" => "array",
            "permissions.*" => "exists:permissions,id",
        ]);

        $user->permissions()->sync($data["permissions"] ?? []);

        return redirect()->route("admin.settings.rights.edit", $user)
            ->with("success", "Rights updated for {$user->name}.");
    }

    public function resetPassword(Request $request, User $user, \App\Services\AccountProvisioningService $accounts)
    {
        // No password field here on purpose — an admin can trigger a reset,
        // but the new password is generated and emailed directly to the
        // account owner, never shown to the admin. See
        // App\Services\AccountProvisioningService.
        $newPassword = Str::password(12);

        $user->update(["password" => Hash::make($newPassword), "must_change_password" => true]);

        $accounts->sendCredentials($user, $newPassword);

        return back()->with("success", "Password reset for {$user->name}. A new temporary password has been emailed to them.");
    }
}
