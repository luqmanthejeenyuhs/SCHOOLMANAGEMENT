<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("supplier_bill_payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("supplier_bill_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount", 12, 2);
            $table->date("payment_date");
            $table->string("method")->default("bank");
            $table->string("reference")->nullable();
            $table->foreignId("paid_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("supplier_bill_payments");
    }
};
