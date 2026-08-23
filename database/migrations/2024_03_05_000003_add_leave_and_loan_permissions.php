<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("permissions")->upsert([
            [
                "key" => "manage_leave_requests",
                "name" => "Approve/Reject Staff Leave Requests",
                "category" => "HR",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "key" => "manage_loan_requests",
                "name" => "Approve/Reject Staff Loans & Advances",
                "category" => "HR",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ], ["key"], ["name", "category", "updated_at"]);

        // Anyone already trusted with staff attendance is a reasonable
        // default to also review leave/loans — same upgrade-safety pattern
        // as the earlier permission migrations, so nobody with HR access
        // today gets locked out of the new screens tomorrow.
        $newPermissionIds = DB::table("permissions")
            ->whereIn("key", ["manage_leave_requests", "manage_loan_requests"])
            ->pluck("id", "key");

        $userIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->whereIn("permissions.key", ["manage_staff_attendance", "generate_payslips"])
            ->distinct()
            ->pluck("permission_user.user_id");

        $rows = [];
        foreach ($userIds as $userId) {
            foreach ($newPermissionIds as $permissionId) {
                $rows[] = [
                    "user_id" => $userId,
                    "permission_id" => $permissionId,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }
        }

        if ($rows) {
            DB::table("permission_user")->upsert($rows, ["user_id", "permission_id"], ["updated_at"]);
        }
    }

    public function down(): void
    {
        DB::table("permissions")->whereIn("key", ["manage_leave_requests", "manage_loan_requests"])->delete();
    }
};
