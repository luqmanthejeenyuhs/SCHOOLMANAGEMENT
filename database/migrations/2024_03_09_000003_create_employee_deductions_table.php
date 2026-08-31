<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("employee_deductions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->foreignId("deduction_type_id")->constrained()->cascadeOnDelete();
            // Overrides the deduction type's default rate_or_amount for this
            // specific employee — e.g. two staff on the same "SACCO Savings"
            // type contributing different amounts.
            $table->decimal("amount", 10, 2);
            $table->boolean("is_active")->default(true);
            $table->timestamps();

            $table->unique(["employee_id", "deduction_type_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("employee_deductions");
    }
};
