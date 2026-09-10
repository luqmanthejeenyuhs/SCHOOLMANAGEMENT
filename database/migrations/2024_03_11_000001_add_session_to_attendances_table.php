<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A student can now be marked present/absent/late separately for the
     * morning and afternoon — a child who arrived fine in the morning but
     * went home sick after lunch shouldn't show as simply "present" or
     * "absent" for the whole day. The old (student_id, date) unique
     * constraint becomes (student_id, date, session) so both marks can
     * coexist. Existing rows are backfilled as "morning" — the only
     * session that existed before this — so nothing already recorded is
     * lost or duplicated.
     */
    public function up(): void
    {
        Schema::table("attendances", function (Blueprint $table) {
            $table->string("session", 20)->default("morning")->after("date");
        });

        // Add the new composite unique index BEFORE dropping the old one —
        // MySQL requires student_id (the foreign key column) to be covered
        // by some index at all times, and briefly having neither in place
        // would fail with "needed in a foreign key constraint".
        Schema::table("attendances", function (Blueprint $table) {
            $table->unique(["student_id", "date", "session"]);
        });

        Schema::table("attendances", function (Blueprint $table) {
            $table->dropUnique(["student_id", "date"]);
        });
    }

    public function down(): void
    {
        // Note: this fails if any student now has both a morning and
        // afternoon record for the same date, since that violates the
        // (student_id, date) constraint being restored — expected for a
        // rollback of a feature that's actually been used, not a bug.
        Schema::table("attendances", function (Blueprint $table) {
            $table->unique(["student_id", "date"]);
        });

        Schema::table("attendances", function (Blueprint $table) {
            $table->dropUnique(["student_id", "date", "session"]);
            $table->dropColumn("session");
        });
    }
};
