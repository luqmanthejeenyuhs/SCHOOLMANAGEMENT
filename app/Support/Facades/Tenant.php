<?php

namespace App\Support\Facades;

use App\Services\TenantManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void resolve(\Illuminate\Http\Request $request)
 * @method static void set(int $schoolId)
 * @method static void forget()
 * @method static int|null id()
 * @method static bool check()
 * @method static \App\Models\School|null current()
 * @method static mixed runFor(int $schoolId, \Closure $callback)
 * @method static mixed runForAll(\Closure $callback)
 * @method static bool bypassed()
 *
 * @see \App\Services\TenantManager
 */
class Tenant extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TenantManager::class;
    }
}
