<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("payments", function (Blueprint $table) {
            // bank_name only applies to method="bank"; reference is generic —
            // bank cheque/transfer ref, M-Pesa transaction code, or card
            // reference, depending on method. Both nullable since "cash"
            // needs neither.
            $table->string("bank_name")->nullable()->after("method");
            $table->string("reference")->nullable()->after("bank_name");
        });
    }

    public function down(): void
    {
        Schema::table("payments", function (Blueprint $table) {
            $table->dropColumn(["bank_name", "reference"]);
        });
    }
};
