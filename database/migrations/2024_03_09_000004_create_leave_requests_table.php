<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This was a duplicate of 2024_03_05_000001_create_leave_requests_table
     * — the same feature got built twice (different column choices:
     * decided_by here vs. reviewed_by/reviewed_at/review_note/days there),
     * and the earlier one is the one that actually ran and is live.
     *
     * Rather than create the table again (which would fail — it already
     * exists) or delete this file (which would break migration history on
     * any environment where it may already be tracked), this is left as a
     * documented no-op. See
     * 2026_09_03_000001_rename_reviewed_by_to_decided_by_on_leave_requests
     * for the actual fix that reconciles the live table with the
     * decided_by column name the real application code uses.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
