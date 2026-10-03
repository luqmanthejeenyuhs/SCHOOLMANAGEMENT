<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __construct(protected TenantManager $tenants)
    {
    }

    /**
     * Mirrors LoginController's showLoginForm(): generic /forgot-password
     * (used by the super_admin, school_id is null) or the branded
     * /school/{school}/forgot-password (or subdomain-resolved) version,
     * which shows that school's logo/name and scopes the username lookup
     * to it — exactly like the login page itself.
     */
    public function show(Request $request, ?School $school = null)
    {
        $school ??= $this->tenants->resolveFromSubdomain($request);

        return view("auth.forgot_password", compact("school"));
    }

    public function send(Request $request, ?School $school = null)
    {
        $school ??= $this->tenants->resolveFromSubdomain($request);

        $data = $request->validate([
            "username" => ["required", "string"],
        ]);

        // school_id is always included (even as null) so the lookup is
        // correctly scoped — username is only unique *per school*, not
        // globally, so a bare username lookup could otherwise match the
        // wrong user. See User::sendPasswordResetNotification() for what
        // actually gets emailed.
        Password::sendResetLink([
            "username" => $data["username"],
            "school_id" => $school?->id,
        ]);

        // Always the same message regardless of whether a matching user
        // was actually found — telling a stranger "no account with that
        // username exists" would let them enumerate real usernames.
        return back()->with("status", "If that username exists, we've sent password reset instructions to the email on file for that account.");
    }
}
