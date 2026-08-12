<?php

namespace App\Models\Scopes;

use App\Support\Facades\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically restricts every query on a tenant-scoped model to the
 * currently resolved school.
 *
 * Fails CLOSED on purpose: if no tenant is resolved (e.g. a queued job or
 * console command that forgot to set one), queries return zero rows rather
 * than silently leaking data across schools. Code that genuinely needs to
 * cross tenants (super-admin reports, artisan commands) must opt in
 * explicitly with Model::withoutGlobalScope(TenantScope::class) or the
 * Tenant::runFor()/Tenant::runForAll() helpers.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Tenant::check()) {
            $builder->where($model->getQualifiedTenantColumn(), "=", Tenant::id());

            return;
        }

        if (Tenant::bypassed()) {
            return;
        }

        // No tenant context and no explicit bypass: don't guess, return nothing.
        $builder->whereRaw("1 = 0");
    }
}
