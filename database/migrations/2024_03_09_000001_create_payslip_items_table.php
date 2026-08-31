<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
