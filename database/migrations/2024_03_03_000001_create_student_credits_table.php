<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("student_credits", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("student_id")->unique()->constrained()->cascadeOnDelete();
            // Running credit balance held on the student's behalf — money
            // received that couldn't be applied to any invoice yet. Backed
            // by the "2000 Student Deposits / Prepaid Fees" liability
            // account; this table is just the fast per-student lookup,
            // the ledger (journal_lines on account 2000) is the source of
            // truth for accounting purposes.
            $table->decimal("balance", 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("student_credits");
    }
};
