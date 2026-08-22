<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("permissions")->upsert([[
            "key" => "manage_accounting",
            "name" => "Manage Accounting (Chart of Accounts, Journal Entries, Ledger)",
            "category" => "Finance",
            "created_at" => now(),
            "updated_at" => now(),
        ]], ["key"], ["name", "category", "updated_at"]);
    }

    public function down(): void
    {
        DB::table("permissions")->where("key", "manage_accounting")->delete();
    }
};
