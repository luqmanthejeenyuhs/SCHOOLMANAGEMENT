<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn("staff_loans", "interest_method")) {
            return;
        }

        Schema::table("staff_loans", function (Blueprint $table) {
            // Kept separate from loan_type (loan vs advance, a *purpose*
            // distinction) — this is *how interest is calculated*.
            // Defaults to "flat" to match every loan created before this
            // column existed, so nothing already active changes behaviour.
            $table->enum("interest_method", ["simple", "compound", "flat"])->default("flat")->after("loan_type");
        });
    }

    public function down(): void
    {
        Schema::table("staff_loans", function (Blueprint $table) {
            $table->dropColumn("interest_method");
        });
    }
};
