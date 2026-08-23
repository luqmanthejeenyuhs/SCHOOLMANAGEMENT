<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("permissions")->upsert([[
            "key" => "manage_staff_attendance",
            "name" => "Mark & Correct Staff Attendance",
            "category" => "HR",
            "created_at" => now(),
            "updated_at" => now(),
        ]], ["key"], ["name", "category", "updated_at"]);

        // Anyone who could already view staff attendance is trusted with HR
        // data — give them the new write permission too on upgrade, rather
        // than silently locking them out until someone re-grants rights.
        $permissionId = DB::table("permissions")->where("key", "manage_staff_attendance")->value("id");

        $userIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->where("permissions.key", "view_staff_attendance")
            ->pluck("permission_user.user_id");

        $rows = [];
        foreach ($userIds as $userId) {
            $rows[] = [
                "user_id" => $userId,
                "permission_id" => $permissionId,
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        if ($rows) {
            DB::table("permission_user")->upsert($rows, ["user_id", "permission_id"], ["updated_at"]);
        }
    }

    public function down(): void
    {
        DB::table("permissions")->where("key", "manage_staff_attendance")->delete();
    }
};
