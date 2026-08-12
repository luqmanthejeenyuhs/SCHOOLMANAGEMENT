<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves "which school is this request for" and stores it on the
 * TenantManager singleton before anything else touches the database.
 *
 * Register this AFTER StartSession (it needs the session for impersonation)
 * and AFTER the auth-loading middleware (it needs $request->user()), in
 * both the 'web' and 'api' middleware groups. See app/Http/Kernel.php.
 */
class IdentifyTenant
{
    public function __construct(protected TenantManager $tenants)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tenants->resolve($request);

        return $next($request);
    }
}
