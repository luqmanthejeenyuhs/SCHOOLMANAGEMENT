<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    protected $subscribe = [
        LogAuthenticationActivity::class,
    ];

    public function boot(): void
    {
        //
    }
}
