<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        return view("admin.settings.index");
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

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            "password" => "nullable|min:6|confirmed",
        ]);

        $newPassword = $data["password"] ?? Str::random(10);

        $user->update(["password" => Hash::make($newPassword)]);

        $message = empty($data["password"])
            ? "Password reset for {$user->name}. Temporary password: {$newPassword}"
            : "Password reset for {$user->name}.";

        return back()->with("success", $message);
    }
}
