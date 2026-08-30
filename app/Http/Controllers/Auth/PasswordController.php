<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Lets any signed-in user (admin, teacher, student, parent, super_admin)
 * change their own password. This is deliberately separate from
 * App\Http\Controllers\Admin\SettingsController@resetPassword, which is the
 * admin-triggered recovery flow — an admin can force a reset, but never
 * sees the resulting password (it's emailed, see
 * App\Mail\PasswordResetMail), and only the account owner can set it here.
 */
class PasswordController extends Controller
{
    public function edit()
    {
        return view("auth.password_edit");
    }

    public function update(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            "current_password" => ["required"],
            "password" => ["required", "min:6", "confirmed"],
        ]);

        $user = Auth::user();

        if (! Hash::check($data["current_password"], $user->password)) {
            return back()->withErrors([
                "current_password" => "Your current password is incorrect.",
            ]);
        }

        $wasForced = $user->must_change_password;

        $user->update(["password" => Hash::make($data["password"]), "must_change_password" => false]);

        $auditLogger->log("password_changed_self", [
            "school_id" => $user->school_id,
            "user_id" => $user->id,
            "username_attempted" => $user->username,
            "description" => "{$user->name} changed their own password.",
        ]);

        if ($wasForced) {
            return redirect(\App\Providers\RouteServiceProvider::redirectByRole())
                ->with("success", "Password set. Welcome in!");
        }

        return back()->with("success", "Your password has been updated.");
    }
}
