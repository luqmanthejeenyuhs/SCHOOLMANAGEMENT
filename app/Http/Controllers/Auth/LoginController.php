<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * GET /login (generic), GET /school/{school}/login (branded, path-based),
     * or any request on a school's own subdomain — e.g.
     * https://greenwood.taalumasms.co.ke/login — resolved automatically by
     * IdentifySchoolFromSubdomain with no path segment or dropdown needed.
     * The path-based route is kept working too, purely as a fallback for
     * anyone using an old link before DNS/DNS caching catches up.
     *
     * When a school is resolved (either way) we show its logo/name instead
     * of the generic Taaluma SMS branding, and remember the school in the
     * session so a validation error on POST still re-renders branded.
     */
    public function showLoginForm(Request $request, ?School $school = null)
    {
        $school ??= $request->attributes->get("subdomainSchool");

        if ($school) {
            if (! $school->is_active) {
                return view("auth.login", [
                    "school" => $school,
                    "suspended" => true,
                ]);
            }

            $request->session()->put("login_school_id", $school->id);
        } else {
            $request->session()->forget("login_school_id");
        }

        return view("auth.login", ["school" => $school, "suspended" => false]);
    }

    /**
     * POST /login, POST /school/{school}/login, or a subdomain request —
     * see showLoginForm() above for how $school is resolved either way.
     *
     * Authenticates primarily by username, but falls back to matching on
     * email if no user matches the username — this is what people already
     * signed up before usernames existed are used to typing, and we don't
     * want to lock anyone out just because they don't yet know (or forgot)
     * the username that was auto-derived for them from their old email
     * during the migration. Both lookups are scoped to the resolved school
     * (or "no school" on the generic /login page, i.e. super_admin only).
     */
    public function login(Request $request, ?School $school = null)
    {
        $school ??= $request->attributes->get("subdomainSchool");

        if ($school && ! $school->is_active) {
            return back()->withErrors([
                "username" => "This school's account is currently suspended. Please contact the platform administrator.",
            ]);
        }

        $data = $request->validate([
            "username" => ["required", "string"],
            "password" => ["required"],
        ]);

        $attempted = Auth::attempt([
            "username" => $data["username"],
            "password" => $data["password"],
            "school_id" => $school?->id,
        ], $request->boolean("remember"));

        // Fallback: the identifier they typed didn't match anyone's
        // username — try it as an email instead, in case that's what
        // they're used to signing in with.
        if (! $attempted && str_contains($data["username"], "@")) {
            $attempted = Auth::attempt([
                "email" => $data["username"],
                "password" => $data["password"],
                "school_id" => $school?->id,
            ], $request->boolean("remember"));
        }

        if ($attempted) {
            $request->session()->regenerate();
            $request->session()->forget("login_school_id");

            Auth::user()->forceFill(["last_login_at" => now()])->save();

            // A system-generated password (every new account, and every
            // admin-triggered reset — see AccountProvisioningService) must
            // be replaced with one only the account owner knows before
            // they can do anything else. EnsurePasswordIsChanged enforces
            // this on every subsequent request too; redirecting straight
            // there on login just avoids the extra bounce.
            if (Auth::user()->must_change_password) {
                return redirect()->route("account.password.edit")
                    ->with("must_change_password", true);
            }

            return redirect()->intended(RouteServiceProvider::redirectByRole());
        }

        return back()->withErrors([
            "username" => "The provided credentials do not match our records.",
        ])->onlyInput("username");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login");
    }
}
