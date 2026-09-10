<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("permissions")->upsert([
            [
                "key" => "manage_suppliers",
                "name" => "Manage Suppliers, Bills & Credit Notes",
                "category" => "Finance",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "key" => "manage_loans",
                "name" => "Manage Staff Loans & Advances",
                "category" => "Finance",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ], ["key"], ["name", "category", "updated_at"]);
    }

    public function down(): void
    {
        DB::table("permissions")->whereIn("key", ["manage_suppliers", "manage_loans"])->delete();
    }
};
