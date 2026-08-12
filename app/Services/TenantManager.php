<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Http\Request;

/**
 * Holds "which school are we currently acting as" for the lifetime of a
 * single request (or a single console invocation). Bound as a singleton —
 * see App\Providers\TenantServiceProvider.
 *
 * Access it via the App\Support\Facades\Tenant facade rather than injecting
 * this class directly, e.g. Tenant::id(), Tenant::current(), Tenant::check().
 */
class TenantManager
{
    protected ?int $schoolId = null;

    protected ?School $school = null;

    protected bool $bypassed = false;

    /**
     * Resolve the current tenant from the request/session for a normal web
     * request. Called by App\Http\Middleware\IdentifyTenant. Priority:
     *
     *  1. A super_admin actively impersonating a school (session flag).
     *  2. The signed-in user's own school_id.
     *  3. No tenant (guest pages, the super-admin platform area).
     */
    public function resolve(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            $this->forget();

            return;
        }

        if (method_exists($user, "isSuperAdmin") && $user->isSuperAdmin()
            && $request->session()->has("impersonating_school_id")) {
            $this->set((int) $request->session()->get("impersonating_school_id"));

            return;
        }

        if ($user->school_id) {
            $this->set((int) $user->school_id);

            return;
        }

        $this->forget();
    }

    public function set(int $schoolId): void
    {
        if ($this->schoolId !== $schoolId) {
            $this->school = null;
        }

        $this->schoolId = $schoolId;
    }

    public function forget(): void
    {
        $this->schoolId = null;
        $this->school = null;
    }

    public function id(): ?int
    {
        return $this->schoolId;
    }

    public function check(): bool
    {
        return $this->schoolId !== null;
    }

    public function current(): ?School
    {
        if (! $this->schoolId) {
            return null;
        }

        return $this->school ??= School::find($this->schoolId);
    }

    /**
     * Run a callback scoped to a specific school regardless of the request's
     * own tenant context — for scheduled jobs, artisan commands, or
     * super-admin actions performed on behalf of a school.
     */
    public function runFor(int $schoolId, \Closure $callback): mixed
    {
        $previous = $this->schoolId;
        $previousBypass = $this->bypassed;

        $this->set($schoolId);
        $this->bypassed = false;

        try {
            return $callback();
        } finally {
            $this->schoolId = $previous;
            $this->bypassed = $previousBypass;
            $this->school = null;
        }
    }

    /**
     * Run a callback with tenant scoping bypassed entirely (cross-school).
     * For super-admin reports and maintenance commands only.
     */
    public function runForAll(\Closure $callback): mixed
    {
        $previous = $this->schoolId;
        $previousBypass = $this->bypassed;

        $this->schoolId = null;
        $this->bypassed = true;

        try {
            return $callback();
        } finally {
            $this->schoolId = $previous;
            $this->bypassed = $previousBypass;
            $this->school = null;
        }
    }

    public function bypassed(): bool
    {
        return $this->bypassed;
    }
}
