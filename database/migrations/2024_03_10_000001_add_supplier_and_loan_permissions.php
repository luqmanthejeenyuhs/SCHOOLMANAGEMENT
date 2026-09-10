<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `manage_suppliers` gates suppliers, supplier bills, and the VAT
     * report. `manage_loans` gates the new ledger-integrated staff loans
     * feature (StaffLoanController) — separate from the existing
     * `manage_loan_requests` permission, which still governs the simpler
     * approve/reject/disburse loan *request* workflow this app already
     * had; the two are intentionally independent features.
     */
    public function up(): void
    {
        $now = now();

        DB::table("permissions")->upsert([
            ["key" => "manage_suppliers", "name" => "Manage Suppliers, Bills & VAT", "category" => "Finance", "created_at" => $now, "updated_at" => $now],
            ["key" => "manage_loans", "name" => "Manage Staff Loans (Ledger-Integrated)", "category" => "HR & Payroll", "created_at" => $now, "updated_at" => $now],
        ], ["key"], ["name", "category", "updated_at"]);

        // Anyone already trusted with the accounting/employees permissions
        // that these features naturally extend gets them for free, rather
        // than starting from zero access.
        $newPermissionIds = DB::table("permissions")
            ->whereIn("key", ["manage_suppliers", "manage_loans"])
            ->pluck("id", "key");

        $accountingUserIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->where("permissions.key", "manage_accounting")
            ->pluck("permission_user.user_id");

        $employeeUserIds = DB::table("permission_user")
            ->join("permissions", "permissions.id", "=", "permission_user.permission_id")
            ->where("permissions.key", "manage_employees")
            ->pluck("permission_user.user_id");

        $rows = [];
        foreach ($accountingUserIds as $userId) {
            $rows[] = ["user_id" => $userId, "permission_id" => $newPermissionIds["manage_suppliers"], "created_at" => $now, "updated_at" => $now];
        }
        foreach ($employeeUserIds as $userId) {
            $rows[] = ["user_id" => $userId, "permission_id" => $newPermissionIds["manage_loans"], "created_at" => $now, "updated_at" => $now];
        }

        if ($rows) {
            DB::table("permission_user")->upsert($rows, ["user_id", "permission_id"], ["updated_at"]);
        }
    }

    public function down(): void
    {
        DB::table("permissions")->whereIn("key", ["manage_suppliers", "manage_loans"])->delete();
    }
};
