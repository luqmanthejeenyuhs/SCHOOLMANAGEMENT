<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A tenant. Every school on the platform is a row here, and almost every
 * other table is scoped to one via a `school_id` column — see
 * App\Models\Concerns\BelongsToTenant for how that scoping works.
 *
 * This model itself is deliberately NOT tenant-scoped (it IS the tenant).
 */
class School extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "slug", "domain", "email", "phone", "address",
        "timezone", "logo_path", "is_active", "trial_ends_at",
        "latitude", "longitude", "geofence_radius_meters",
        "expected_clock_in", "expected_clock_out",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "trial_ends_at" => "datetime",
        "latitude" => "float",
        "longitude" => "float",
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function gradingScales()
    {
        return $this->hasMany(GradingScale::class);
    }

    public function effectiveLatitude(): ?float
    {
        return $this->latitude ?? config("school.latitude");
    }

    public function effectiveLongitude(): ?float
    {
        return $this->longitude ?? config("school.longitude");
    }

    public function effectiveGeofenceRadiusMeters(): int
    {
        return $this->geofence_radius_meters ?? config("school.geofence_radius_meters");
    }
}
