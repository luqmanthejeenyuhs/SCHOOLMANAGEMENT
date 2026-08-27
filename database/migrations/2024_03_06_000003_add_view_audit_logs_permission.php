<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("permissions")->upsert([[
            "key" => "view_audit_logs",
            "name" => "View Audit Trail (logins & security events)",
            "category" => "Settings",
            "created_at" => now(),
            "updated_at" => now(),
        ]], ["key"], ["name", "category", "updated_at"]);

        // Anyone already trusted with general settings access is trusted
        // with the audit trail too, rather than silently locking everyone
        // out until someone re-grants rights.
        $permissionId = DB::table("permissions")->where("key", "view_audit_logs")->value("id");

        $userIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->where("permissions.key", "manage_settings")
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
        DB::table("permissions")->where("key", "view_audit_logs")->delete();
    }
};
