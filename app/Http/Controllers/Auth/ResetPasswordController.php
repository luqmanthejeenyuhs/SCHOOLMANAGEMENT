<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /**
     * The link from PasswordResetMail carries username + school (slug) as
     * query params alongside the token — the form below resubmits them as
     * hidden fields, since Password::reset() needs the same identifying
     * credentials that were used to find the user in the first place (see
     * ForgotPasswordController::send()).
     */
    public function show(Request $request, string $token)
    {
        $username = $request->query("username", "");
        $schoolSlug = $request->query("school");
        $school = $schoolSlug ? School::where("slug", $schoolSlug)->first() : null;

        return view("auth.reset_password", compact("token", "username", "school"));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            "token" => ["required", "string"],
            "username" => ["required", "string"],
            "school_slug" => ["nullable", "string"],
            "password" => ["required", "confirmed", "min:8"],
        ]);

        $school = $data["school_slug"] ? School::where("slug", $data["school_slug"])->first() : null;

        $status = Password::reset(
            [
                "username" => $data["username"],
                "school_id" => $school?->id,
                "token" => $data["token"],
                "password" => $data["password"],
                "password_confirmation" => $request->input("password_confirmation"),
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    "password" => Hash::make($password),
                    // A password they just chose themselves doesn't need
                    // to be changed again on next login.
                    "must_change_password" => false,
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                "password" => "This reset link is invalid or has expired. Please request a new one.",
            ]);
        }

        $loginRoute = $school ? route("login.school", $school) : route("login");

        return redirect($loginRoute)->with("status", "Your password has been reset. You can now sign in.");
    }
}
