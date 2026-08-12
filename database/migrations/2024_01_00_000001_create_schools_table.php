<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The tenants table. Every school using this platform gets one row here,
 * and (almost) every other table in the system is scoped to a school via
 * a `school_id` foreign key — see App\Models\Concerns\BelongsToTenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create("schools", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            // URL-friendly identifier, e.g. for a future greenwood.yourapp.test subdomain.
            $table->string("slug")->unique();
            // Optional fully custom domain a school can point at their tenant.
            $table->string("domain")->nullable()->unique();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->string("address")->nullable();
            $table->string("timezone")->default("Africa/Nairobi");
            $table->string("logo_path")->nullable();
            // Attendance geofence policy — used to vary hardcoded in
            // config/school.php pre-tenancy; now each school sets its own.
            $table->decimal("latitude", 10, 7)->nullable();
            $table->decimal("longitude", 10, 7)->nullable();
            $table->unsignedInteger("geofence_radius_meters")->default(200);
            $table->time("expected_clock_in")->default("08:00");
            $table->time("expected_clock_out")->default("16:00");
            $table->boolean("is_active")->default(true);
            $table->timestamp("trial_ends_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("schools");
    }
};
