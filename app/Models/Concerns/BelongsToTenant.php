<?php

namespace App\Models\Concerns;

use App\Models\School;
use App\Models\Scopes\TenantScope;
use App\Support\Facades\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * Add this trait to any model whose table has a `school_id` column to make
 * it tenant-aware:
 *
 *  - Every query is automatically filtered to the current school
 *    (see TenantScope) — no need to add ->where('school_id', ...) yourself.
 *  - New records are automatically stamped with the current school_id when
 *    you don't set one explicitly, so existing Model::create([...]) calls
 *    in controllers keep working unmodified.
 *  - Implicit route-model binding (Route::model / type-hinted controller
 *    params) goes through the same scoped query, so a stray ID belonging to
 *    another school 404s instead of leaking through.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->school_id) && Tenant::check()) {
                $model->school_id = Tenant::id();
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function getQualifiedTenantColumn(): string
    {
        return $this->qualifyColumn("school_id");
    }

    /**
     * Query across every school, bypassing tenant scoping entirely.
     * For super-admin reporting and console commands only — never use this
     * to serve a single school's request.
     */
    public function scopeAllSchools(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
