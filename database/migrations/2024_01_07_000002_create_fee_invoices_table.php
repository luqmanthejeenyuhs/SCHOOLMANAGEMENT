<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("fee_invoices", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->foreignId("fee_type_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount", 10, 2);
            $table->date("due_date")->nullable();
            $table->enum("status", ["unpaid", "partially_paid", "paid"])->default("unpaid");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("fee_invoices");
    }
};
