<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, "You do not have access to this section.");
        }

        // A super_admin's own `role` column never changes while
        // impersonating a school (see TenantManager) — without this check
        // they'd pass IdentifyTenant's tenant resolution just fine but then
        // get bounced by this middleware on every admin-only page, since
        // their literal role is still "super_admin", not "admin".
        if ($user->isSuperAdmin() && session()->has("impersonating_school_id") && in_array("admin", $roles)) {
            return $next($request);
        }

        if (! in_array($user->role, $roles)) {
            abort(403, "You do not have access to this section.");
        }

        return $next($request);
    }
}
