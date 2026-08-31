<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `must_change_password` is set true whenever
     * App\Services\AccountProvisioningService generates a password for
     * someone (every new teacher/student/parent/admin account, and every
     * admin-triggered reset) — since that password is only ever seen by
     * the account owner via email, forcing a change on first use means
     * the temporary, system-generated password doesn't linger as their
     * permanent one. Enforced by App\Http\Middleware\EnsurePasswordIsChanged.
     *
     * `last_login_at` is a simple "when did this person last sign in"
     * timestamp, updated by App\Http\Controllers\Auth\LoginController.
     *
     * Guarded with hasColumn() checks because a separately-built update
     * package may have already added one or both of these same columns
     * under a different migration name — this way the migration works
     * whether this project already has them or not, instead of failing
     * with "duplicate column" on one setup and "column not found" on
     * another.
     */
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (! Schema::hasColumn("users", "must_change_password")) {
                $table->boolean("must_change_password")->default(false)->after("password");
            }

            if (! Schema::hasColumn("users", "last_login_at")) {
                $table->timestamp("last_login_at")->nullable()->after(
                    Schema::hasColumn("users", "must_change_password") ? "must_change_password" : "password"
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (Schema::hasColumn("users", "must_change_password")) {
                $table->dropColumn("must_change_password");
            }

            if (Schema::hasColumn("users", "last_login_at")) {
                $table->dropColumn("last_login_at");
            }
        });
    }
};
