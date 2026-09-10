<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The leave_requests table that's actually live was created by
     * 2024_03_05_000001_create_leave_requests_table, which used the column
     * name "reviewed_by". But App\Models\LeaveRequest and
     * Admin\LeaveRequestController — the code actually wired to real
     * routes — use "decided_by" instead (a second, never-run duplicate
     * migration, 2024_03_09_000004, was written with that name and never
     * reconciled with the first one). This renames the column to match the
     * code, preserving any existing data rather than dropping and
     * recreating the table.
     *
     * reviewed_at / review_note / days are left in place, unused but
     * harmless — dropping columns that might hold real historical data
     * isn't worth it just for tidiness.
     */
    public function up(): void
    {
        if (Schema::hasColumn('leave_requests', 'reviewed_by') && ! Schema::hasColumn('leave_requests', 'decided_by')) {
            // Using raw SQL rather than Schema::renameColumn() — that
            // method needs doctrine/dbal installed, which this project
            // doesn't have, so it would throw immediately.
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE leave_requests CHANGE reviewed_by decided_by BIGINT UNSIGNED NULL'
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leave_requests', 'decided_by') && ! Schema::hasColumn('leave_requests', 'reviewed_by')) {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE leave_requests CHANGE decided_by reviewed_by BIGINT UNSIGNED NULL'
            );
        }
    }
};
