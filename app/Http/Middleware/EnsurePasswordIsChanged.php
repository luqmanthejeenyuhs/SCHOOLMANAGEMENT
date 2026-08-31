<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Applies to every authenticated route. A school's first admin (created
     * by a super admin via SchoolController@store) has must_change_password
     * = true and a randomly-generated password only they ever saw, in their
     * email — this forces them through /change-password before touching
     * anything else, so they end up with a real password only they know.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $exempt = $request->routeIs('password.change')
            || $request->routeIs('password.update')
            || $request->routeIs('logout');

        if ($user && $user->must_change_password && ! $exempt) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
