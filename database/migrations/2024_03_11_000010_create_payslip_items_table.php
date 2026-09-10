<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded: this table may already exist under an earlier
        // migration filename from a separately-delivered update
        // package — renaming files to avoid a collision doesn't
        // change what Laravel tracks by filename, so without this
        // check a rename alone would try to re-create an existing table.
        if (Schema::hasTable("payslip_items")) {
            return;
        }

        Schema::create("payslip_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("payslip_id")->constrained()->cascadeOnDelete();
            $table->string("label");
            $table->enum("category", ["loan_repayment", "custom_deduction", "unpaid_leave", "other"])->default("other");
            $table->decimal("amount", 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payslip_items");
    }
};
