<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Students sign in with their admission number instead of a separate
     * chosen username (see App\Http\Controllers\Admin\StudentController,
     * which now sets username = admission_no automatically). This backfills
     * every existing student's username to match their current admission
     * number, replacing whatever was derived from their email earlier.
     *
     * Safe to run more than once, and safe even though (school_id,
     * username) is unique — admission_no is already enforced unique per
     * school, so there's nothing for it to collide with.
     */
    public function up(): void
    {
        $students = DB::table("students")
            ->join("users", "users.id", "=", "students.user_id")
            ->select("students.user_id", "students.admission_no")
            ->get();

        foreach ($students as $row) {
            if ($row->admission_no === null || $row->admission_no === "") {
                continue;
            }

            DB::table("users")->where("id", $row->user_id)->update(["username" => $row->admission_no]);
        }
    }

    public function down(): void
    {
        // Not reversible — the pre-image (email-derived) usernames aren't
        // retained anywhere to restore.
    }
};
