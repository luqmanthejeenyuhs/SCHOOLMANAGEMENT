<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the platform-level super-admin area (managing schools themselves,
 * as opposed to a single school's data). Apply to the /superadmin/* route
 * group: Route::middleware(['auth', 'super_admin'])->prefix('superadmin')...
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, "isSuperAdmin") || ! $user->isSuperAdmin()) {
            abort(403, "Super admin access only.");
        }

        return $next($request);
    }
}
