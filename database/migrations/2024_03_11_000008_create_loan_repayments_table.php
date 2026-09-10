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
        if (Schema::hasTable("loan_repayments")) {
            return;
        }

        Schema::create("loan_repayments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("staff_loan_id")->constrained()->cascadeOnDelete();
            // Set when this repayment came from an automatic payslip
            // deduction; null for a manual cash repayment recorded directly
            // against the loan (e.g. employee pays outside payroll).
            $table->foreignId("payslip_id")->nullable()->constrained()->nullOnDelete();
            $table->decimal("amount", 12, 2);
            $table->decimal("principal_portion", 12, 2);
            $table->decimal("interest_portion", 12, 2);
            $table->date("payment_date");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("loan_repayments");
    }
};
