<?php

namespace App\Providers;

use App\Models\School;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = "/dashboard";

    public function boot(): void
    {
        RateLimiter::for("login", function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware("web")
                ->group(base_path("routes/web.php"));
        });
    }

    /**
     * Redirect a freshly authenticated user to the right dashboard for their role.
     *
     * Every school-level page lives under /{school}/..., so the school's slug
     * must be part of the URL. Only the super admin area (/superadmin/...) has
     * no slug.
     */
    public static function redirectByRole(): string
    {
        $user = Auth::user();

        if (! $user) {
            return route("login");
        }

        $isSuperAdmin = method_exists($user, "isSuperAdmin") && $user->isSuperAdmin();

        // A super_admin's own role never changes while impersonating — the
        // impersonated school lives only in the session (see TenantManager) —
        // so check that first, or they'd be bounced straight back to the
        // platform schools list instead of the school's own dashboard.
        if ($isSuperAdmin && session()->has("impersonating_school_id")) {
            $impersonatedSlug = School::find(session("impersonating_school_id"))?->slug;

            if ($impersonatedSlug) {
                return route("admin.dashboard", ["school" => $impersonatedSlug]);
            }
        }

        if ($user->role === "super_admin") {
            return route("superadmin.dashboard");
        }

        // Every other role belongs to a school, and the school slug is
        // required to build any of their URLs.
        $slug = $user->school_id ? School::find($user->school_id)?->slug : null;

        abort_if(! $slug, 403, "Your account is not linked to a school.");

        // The default case aborts instead of redirecting on purpose: sending an
        // unknown role to another URL would bounce through DashboardController
        // or RedirectIfAuthenticated and back here, creating a redirect loop.
        return match ($user->role) {
            "admin"   => route("admin.dashboard",   ["school" => $slug]),
            "teacher" => route("teacher.dashboard", ["school" => $slug]),
            "student" => route("student.dashboard", ["school" => $slug]),
            "parent"  => route("parent.dashboard",  ["school" => $slug]),
            default   => abort(403, "Your role has no dashboard."),
        };
    }
}