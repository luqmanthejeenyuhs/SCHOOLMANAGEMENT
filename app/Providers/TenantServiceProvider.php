<?php

namespace App\Providers;

use App\Models\School;
use App\Observers\SchoolObserver;
use App\Services\TenantManager;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
    }

    public function boot(): void
    {
        School::observe(SchoolObserver::class);
    }
}
