<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The Activities and Library tabs added to a student's profile (see
     * App\Http\Controllers\Admin\StudentController@show and
     * resources/views/admin/students/show.blade.php) were previously only
     * gated by the same broad `view_students` permission as the rest of
     * the page. This gives them their own checkboxes under the "Students"
     * category in Settings → User Rights, so an admin can grant or
     * withhold them independently — e.g. a librarian-only admin account
     * could get view_student_library without the rest of the tabs.
     *
     * Managing (enrolling/removing) activities already has its own
     * permission (`manage_activities`, under Administrative) — these two
     * are specifically about *viewing* a student's activity/library
     * history on their profile.
     */
    public function up(): void
    {
        $now = now();

        DB::table("permissions")->upsert([
            ["key" => "view_student_activities", "name" => "View Student Activities", "category" => "Students", "created_at" => $now, "updated_at" => $now],
            ["key" => "view_student_library", "name" => "View Student Library Records", "category" => "Students", "created_at" => $now, "updated_at" => $now],
        ], ["key"], ["name", "category", "updated_at"]);

        // Anyone who can already view students at all should be able to
        // see these two additional tabs too, rather than losing access to
        // part of a page they could already open.
        $newPermissionIds = DB::table("permissions")
            ->whereIn("key", ["view_student_activities", "view_student_library"])
            ->pluck("id");

        $userIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->where("permissions.key", "view_students")
            ->pluck("permission_user.user_id");

        $rows = [];
        foreach ($userIds as $userId) {
            foreach ($newPermissionIds as $permissionId) {
                $rows[] = [
                    "user_id" => $userId,
                    "permission_id" => $permissionId,
                    "created_at" => $now,
                    "updated_at" => $now,
                ];
            }
        }

        if ($rows) {
            DB::table("permission_user")->upsert($rows, ["user_id", "permission_id"], ["updated_at"]);
        }
    }

    public function down(): void
    {
        DB::table("permissions")->whereIn("key", ["view_student_activities", "view_student_library"])->delete();
    }
};
