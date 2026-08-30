<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("staff_loans", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            // "advance" is typically short-term / next-payslip, no interest;
            // "loan" is longer-term and usually carries interest — kept as
            // one table with a type flag since they share the same
            // repayment-via-payroll mechanism.
            $table->enum("loan_type", ["loan", "advance"])->default("loan");
            $table->decimal("principal", 12, 2);
            $table->decimal("interest_rate", 5, 2)->default(0);
            $table->unsignedSmallInteger("repayment_period_months");
            // Derived at creation time from principal/rate/period using
            // simple interest, then stored — so it never silently changes if
            // the interest_rate column value is edited later on an active loan.
            $table->decimal("total_interest", 12, 2)->default(0);
            $table->decimal("total_repayable", 12, 2);
            $table->decimal("monthly_installment", 12, 2);
            $table->decimal("balance_remaining", 12, 2);
            $table->date("start_date");
            $table->enum("status", ["active", "completed", "written_off"])->default("active");
            $table->foreignId("approved_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("staff_loans");
    }
};
