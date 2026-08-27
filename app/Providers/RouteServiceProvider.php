<?php

namespace App\Providers;

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
     */
    public static function redirectByRole(): string
    {
        $user = Auth::user();

        if (! $user) {
            return "/login";
        }

        // A super_admin's own role never changes while impersonating — the
        // impersonated school lives only in the session (see TenantManager)
        // — so check that first, or they'd be bounced straight back to the
        // platform schools list instead of the school's own dashboard.
        if (method_exists($user, "isSuperAdmin") && $user->isSuperAdmin()
            && session()->has("impersonating_school_id")) {
            return "/admin/dashboard";
        }

        return match ($user->role) {
            "super_admin" => "/superadmin/schools",
            "admin" => "/admin/dashboard",
            "teacher" => "/teacher/dashboard",
            "student" => "/student/dashboard",
            default => "/dashboard",
        };
    }
}
