<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Until now a timetable slot could only ever be "this teacher teaches
     * this subject to this class" — there was no way to represent a free
     * period, duty, marking/prep time, a club, sports, or a break/lunch
     * block, all of which a real teacher's weekly timetable needs.
     *
     * section_id and subject_id become nullable (only meaningful for
     * slot_type = 'lesson'); a new `label` column carries free-text for
     * everything else ("Duty / Admin", "Basketball Club", ...).
     * teacher_id stays required for personal slot types, but break/lunch
     * are school-wide — the same block applies to everyone — so those are
     * stored once per school with teacher_id left null, and every
     * teacher's timetable view overlays them automatically. school_id is
     * added directly (rather than inferred through section/teacher, which
     * can now both be null) so break/lunch rows are still tenant-scoped.
     */
    public function up(): void
    {
        Schema::table("timetable_slots", function (Blueprint $table) {
            $table->foreignId("school_id")->nullable()->after("id")->constrained()->cascadeOnDelete();
            $table->string("slot_type", 20)->default("lesson")->after("teacher_id");
            $table->string("label")->nullable()->after("slot_type");
        });

        // section_id/subject_id/teacher_id only make sense for slot_type =
        // 'lesson'; a free period, duty, or school-wide break has none of
        // them. Laravel's ->nullable()->change() needs doctrine/dbal, which
        // isn't installed here, so this is a raw ALTER instead — MySQL
        // allows dropping NOT NULL without touching the existing foreign
        // key constraints on these columns.
        DB::statement("ALTER TABLE timetable_slots MODIFY section_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE timetable_slots MODIFY subject_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE timetable_slots MODIFY teacher_id BIGINT UNSIGNED NULL");

        // Backfill school_id for every existing (lesson) row from its section.
        DB::statement("
            UPDATE timetable_slots
            JOIN sections ON sections.id = timetable_slots.section_id
            SET timetable_slots.school_id = sections.school_id
            WHERE timetable_slots.school_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table("timetable_slots", function (Blueprint $table) {
            $table->dropConstrainedForeignId("school_id");
            $table->dropColumn(["slot_type", "label"]);
        });
    }
};
