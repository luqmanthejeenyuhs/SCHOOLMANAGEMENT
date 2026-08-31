<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(protected TenantManager $tenants)
    {
    }

    public function showLoginForm(Request $request)
    {
        // If this is e.g. littleheaven.taalumasms.co.ke, brand the login
        // page with that school's name/logo. On the plain platform domain
        // (or an unrecognised subdomain), $school is just null and the page
        // falls back to generic "Taaluma SMS" branding.
        $school = $this->tenants->resolveFromSubdomain($request);

        return view("auth.login", compact("school"));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required"],
        ]);

        if (! Auth::attempt($credentials, $request->boolean("remember"))) {
            return back()->withErrors([
                "email" => "The provided credentials do not match our records.",
            ])->onlyInput("email");
        }

        $school = $this->tenants->resolveFromSubdomain($request);

        // Logged in on a specific school's subdomain, but with an account
        // that belongs to a different school (or no school, and isn't the
        // platform super admin) — reject rather than silently letting them
        // in under the wrong tenant context.
        if ($school && ! Auth::user()->isSuperAdmin() && (int) Auth::user()->school_id !== $school->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                "email" => "These credentials aren't linked to {$school->name}. Check you're on the right school's login page.",
            ])->onlyInput("email");
        }

        $request->session()->regenerate();

        Auth::user()->update(["last_login_at" => now()]);

        return redirect()->intended(RouteServiceProvider::redirectByRole());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login");
    }
}
