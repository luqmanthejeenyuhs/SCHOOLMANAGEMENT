<?php

/*
|--------------------------------------------------------------------------
| School Location & Attendance Policy
|--------------------------------------------------------------------------
| Used by the teacher self-clock-in page to confirm a staff member is
| physically on the compound before accepting their check-in (a lightweight
| software equivalent of a biometric gate terminal), and to flag late
| arrivals on the admin attendance dashboard.
|
| Set SCHOOL_LATITUDE / SCHOOL_LONGITUDE to your compound's coordinates
| (e.g. from Google Maps: right-click the location → the numbers shown).
*/

return [
    'latitude' => env('SCHOOL_LATITUDE', -1.286389),
    'longitude' => env('SCHOOL_LONGITUDE', 36.817223),

    // Anyone clocking in from further than this from the compound is rejected.
    'geofence_radius_meters' => env('SCHOOL_GEOFENCE_RADIUS', 200),

    // Clocking in after this time (24hr, school timezone) is marked "late".
    'expected_clock_in' => env('SCHOOL_EXPECTED_CLOCK_IN', '08:00'),
    'expected_clock_out' => env('SCHOOL_EXPECTED_CLOCK_OUT', '16:00'),
];
