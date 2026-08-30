<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            // True for the first admin created by a super admin via
            // SchoolController@store, whose real password only the school
            // itself ever sees (emailed directly, generated randomly, never
            // shown in the UI). Cleared once they set their own password.
            $table->boolean("must_change_password")->default(false)->after("password");
            $table->timestamp("last_login_at")->nullable()->after("must_change_password");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["must_change_password", "last_login_at"]);
        });
    }
};
