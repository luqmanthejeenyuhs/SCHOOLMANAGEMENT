<?php

/**
 * NOTE: this project's routes/web.php was not included in the upload this
 * change was based on, so it couldn't be located and edited directly.
 * Add the block below into your real routes/web.php (inside the existing
 * 'auth' middleware group, alongside your admin/teacher/student route
 * groups) to wire up the new super-admin tenant-management area.
 */

use App\Http\Controllers\SuperAdmin\ImpersonationController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth", "super_admin"])
    ->prefix("superadmin")
    ->name("superadmin.")
    ->group(function () {
        Route::resource("schools", SchoolController::class);
        Route::post("schools/{school}/toggle-active", [SchoolController::class, "toggleActive"])
            ->name("schools.toggle-active");
        Route::post("schools/{school}/impersonate", [ImpersonationController::class, "start"])
            ->name("schools.impersonate");
        Route::post("stop-impersonating", [ImpersonationController::class, "stop"])
            ->name("stop-impersonating");
    });
