<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Self-service password change — every role (admin, teacher, student,
 * parent) reaches this the same way, whether voluntarily (the "Password"
 * button in the nav) or because they're forced to (must_change_password,
 * e.g. right after an admin creates their account with a system-generated
 * password — see App\Http\Middleware\EnsurePasswordIsChanged). The admin
 * never sees or is notified of the new password chosen here — that's the
 * whole point of it being self-service.
 */
class AccountController extends Controller
{
    public function editPassword()
    {
        return view("account.password");
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            "current_password" => ["required", "current_password"],
            "password" => ["required", "confirmed", Password::min(8)],
        ]);

        $request->user()->update([
            "password" => Hash::make($data["password"]),
            "must_change_password" => false,
        ]);

        return redirect()->route("dashboard")->with("success", "Password updated.");
    }
}
