<?php

/*
|--------------------------------------------------------------------------
| School Location & Attendance Policy (fallback defaults)
|--------------------------------------------------------------------------
| NOTE: now that the app is multi-tenant, each school's compound location
| and clock-in policy lives on its own `schools` row (see
| App\Models\School: latitude, longitude, geofence_radius_meters,
| expected_clock_in, expected_clock_out) rather than here. Use
| Tenant::current() to read a school's own values; these config values are
| only a fallback for a school that hasn't set its own yet, or for
| single-tenant/local testing.
*/

return [
    'latitude' => env('SCHOOL_LATITUDE', -1.286389),
    'longitude' => env('SCHOOL_LONGITUDE', 36.817223),

    // Anyone clocking in from further than this from the compound is rejected.
    'geofence_radius_meters' => env('SCHOOL_GEOFENCE_RADIUS', 200),

    // Clocking in after this time (24hr, school timezone) is marked "late".
    'expected_clock_in' => env('SCHOOL_EXPECTED_CLOCK_IN', '08:00'),
    'expected_clock_out' => env('SCHOOL_EXPECTED_CLOCK_OUT', '16:00'),

    // The platform's own root domain, e.g. "taalumasms.co.ke" — used to
    // detect a school subdomain like "littleheaven.taalumasms.co.ke" by
    // stripping this off the request host. Set via .env; leave unset (null)
    // locally / on the default *.laravel.cloud host, where there's no real
    // subdomain-per-school routing to do.
    'platform_domain' => env('PLATFORM_DOMAIN'),
];
