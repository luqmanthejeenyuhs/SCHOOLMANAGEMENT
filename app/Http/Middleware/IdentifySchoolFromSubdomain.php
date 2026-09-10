<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifySchoolFromSubdomain
{
    /**
     * Resolves a School purely from the request's hostname — e.g.
     * https://greenwood.taalumasms.co.ke — with no user logged in yet, and
     * makes it available to the rest of the request as
     * $request->attributes->get('subdomainSchool').
     *
     * Used by LoginController to brand the login page and to scope
     * Auth::attempt() to the right school automatically, with zero path
     * segments or dropdowns needed — the subdomain *is* the school.
     *
     * On the bare platform domain (taalumasms.co.ke) or an unrecognised
     * subdomain, this resolves to null and login falls back to super-admin
     * behaviour (no school), exactly as before.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $platformDomain = config('school.platform_domain');

        if ($platformDomain) {
            $host = $request->getHost();

            if (str_ends_with($host, ".{$platformDomain}")) {
                $subdomain = substr($host, 0, -strlen(".{$platformDomain}"));

                if ($subdomain !== '' && ! in_array($subdomain, ['www', 'app'])) {
                    $school = School::where('slug', $subdomain)->first();

                    if ($school) {
                        $request->attributes->set('subdomainSchool', $school);
                    }
                }
            }
        }

        return $next($request);
    }
}
