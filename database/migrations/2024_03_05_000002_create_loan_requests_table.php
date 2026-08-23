<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("loan_requests", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->enum("request_type", ["loan", "salary_advance"]);
            $table->decimal("amount", 12, 2);
            $table->text("reason")->nullable();
            // 'disbursed' is a separate step from 'approved' on purpose —
            // approval is a decision, disbursed means the money has
            // actually left the account, which an admin marks once it has.
            $table->enum("status", ["pending", "approved", "rejected", "disbursed"])->default("pending");
            $table->foreignId("reviewed_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamp("reviewed_at")->nullable();
            $table->string("review_note")->nullable();
            $table->timestamp("disbursed_at")->nullable();
            $table->timestamps();

            $table->index(["school_id", "employee_id", "status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("loan_requests");
    }
};
