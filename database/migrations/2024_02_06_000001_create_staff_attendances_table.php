<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("staff_attendances", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->date("date");
            $table->dateTime("clock_in")->nullable();
            $table->dateTime("clock_out")->nullable();
            // manual = admin/teacher entered it directly; geofence = self clock-in
            // that passed the on-compound GPS check.
            $table->enum("method", ["manual", "geofence"])->default("manual");
            $table->enum("status", ["present", "late", "absent", "on_leave", "half_day"])->default("present");
            $table->foreignId("marked_by")->nullable()->constrained("users")->nullOnDelete();
            $table->string("remarks")->nullable();
            $table->timestamps();

            $table->unique(["employee_id", "date"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("staff_attendances");
    }
};
