<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\StaffAttendance;
use App\Models\User;
use RuntimeException;

/**
 * How staff attendance is captured, in two ways:
 *
 *  1. Self clock-in (Teacher\ClockController, /staff/clock and /teacher/clock)
 *     — a staff member with a linked Employee record clocks themself in/out
 *     from their phone or the office computer. If the browser provides GPS
 *     coordinates, we check them against the school's compound location
 *     (School::latitude/longitude/geofence_radius_meters) and mark the
 *     record method=geofence when they're actually on-site, or method=manual
 *     with a distance note when they're not (or GPS wasn't available at
 *     all) — we still record the clock-in either way rather than blocking
 *     it, since GPS failures shouldn't stop someone from getting to work.
 *
 *  2. Admin manual entry (Admin\StaffAttendanceController) — for staff who
 *     don't have a login (many support-staff roles won't), or to correct a
 *     mistaken self clock-in. Always method=manual, with marked_by set to
 *     whichever admin entered it.
 *
 * Either way, "late" is computed automatically by comparing the clock-in
 * time against the school's expected_clock_in (with a small grace period),
 * so nobody has to eyeball a clock and decide.
 */
class StaffAttendanceService
{
    // Minutes after the school's expected_clock_in before a self clock-in
    // is marked "late" rather than "present" — avoids flagging someone
    // present at 8:03 for an 8:00 start.
    protected const GRACE_MINUTES = 10;

    public function clockIn(Employee $employee, ?float $lat = null, ?float $lng = null): StaffAttendance
    {
        $record = StaffAttendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => today()->toDateString(),
        ]);

        if ($record->exists && $record->clock_in) {
            throw new RuntimeException('Already clocked in today at '.$record->clock_in->format('g:i A').'.');
        }

        [$method, $remarks] = $this->resolveMethod($employee, $lat, $lng);

        $record->fill([
            'clock_in' => now(),
            'method' => $method,
            'status' => $this->statusForClockIn($employee, now()),
            'remarks' => $remarks,
        ])->save();

        return $record;
    }

    public function clockOut(Employee $employee, ?float $lat = null, ?float $lng = null): StaffAttendance
    {
        $record = StaffAttendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if (! $record || ! $record->clock_in) {
            throw new RuntimeException("You haven't clocked in yet today.");
        }

        if ($record->clock_out) {
            throw new RuntimeException('Already clocked out today at '.$record->clock_out->format('g:i A').'.');
        }

        $record->update(['clock_out' => now()]);

        return $record;
    }

    /**
     * An admin marking or correcting attendance directly — always
     * method=manual, always attributed to whoever did it.
     */
    public function markManually(
        Employee $employee,
        string $date,
        ?string $clockIn,
        ?string $clockOut,
        string $status,
        User $markedBy,
        ?string $remarks = null,
    ): StaffAttendance {
        $record = StaffAttendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $date,
        ]);

        $record->fill([
            'clock_in' => $clockIn ? $date.' '.$clockIn : null,
            'clock_out' => $clockOut ? $date.' '.$clockOut : null,
            'status' => $status,
            'method' => 'manual',
            'marked_by' => $markedBy->id,
            'remarks' => $remarks,
        ])->save();

        return $record;
    }

    protected function statusForClockIn(Employee $employee, \DateTimeInterface $clockInAt): string
    {
        $school = $employee->school;
        $expected = $school?->expected_clock_in;

        if (! $expected) {
            return 'present';
        }

        $deadline = \Carbon\Carbon::parse($clockInAt)->setTimeFromTimeString($expected)->addMinutes(self::GRACE_MINUTES);

        return \Carbon\Carbon::parse($clockInAt)->greaterThan($deadline) ? 'late' : 'present';
    }

    /**
     * @return array{0: string, 1: ?string} [method, remarks]
     */
    protected function resolveMethod(Employee $employee, ?float $lat, ?float $lng): array
    {
        $school = $employee->school;

        if ($lat === null || $lng === null) {
            return ['manual', 'Clocked in without location (GPS unavailable or permission denied).'];
        }

        $schoolLat = $school?->effectiveLatitude();
        $schoolLng = $school?->effectiveLongitude();

        if ($schoolLat === null || $schoolLng === null) {
            return ['manual', 'Location captured, but the school compound location isn\'t configured yet — ask an admin to set it in Settings.'];
        }

        $distance = $this->distanceMeters($lat, $lng, (float) $schoolLat, (float) $schoolLng);
        $radius = $school->effectiveGeofenceRadiusMeters();

        if ($distance <= $radius) {
            return ['geofence', 'On-compound ('.round($distance).'m from centre).'];
        }

        return ['manual', 'Outside the school geofence ('.round($distance).'m away, limit '.$radius.'m) — flagged for review.'];
    }

    /**
     * Great-circle distance between two lat/lng points, in metres.
     */
    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
