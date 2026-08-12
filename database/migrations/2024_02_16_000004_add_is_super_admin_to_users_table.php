<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->boolean("is_super_admin")->default(false)->after("role");
        });

        // Grandfather in every admin account that already exists at the time this
        // migration runs, so nobody currently using the system gets locked out.
        // New admin accounts created afterwards start with NO rights until someone
        // assigns them via Settings > User Rights.
        DB::table("users")->where("role", "admin")->update(["is_super_admin" => true]);
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("is_super_admin");
        });
    }
};
