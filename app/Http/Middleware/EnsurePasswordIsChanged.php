<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every account created by App\Services\AccountProvisioningService (new
 * teachers/students/parents/admins, and admin-triggered password resets)
 * gets a randomly-generated password that only the account owner ever
 * sees, by email. must_change_password stays true until they replace it
 * themselves via the self-service page — this middleware blocks everything
 * else in the meantime so a stale, system-generated password can't become
 * someone's permanent one just because they never got around to changing it.
 */
class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $exempt = $request->routeIs("account.password.edit")
            || $request->routeIs("account.password.update")
            || $request->routeIs("logout");

        if ($user && $user->must_change_password && ! $exempt) {
            return redirect()->route("account.password.edit")
                ->with("must_change_password", true);
        }

        return $next($request);
    }
}
