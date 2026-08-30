<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("credit_notes", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            // Nullable: a credit note can apply against a specific bill (the
            // common case — returned goods, overcharge) or sit as a general
            // credit against the supplier to be applied to a future bill.
            $table->foreignId("supplier_bill_id")->nullable()->constrained()->nullOnDelete();
            $table->decimal("amount", 12, 2);
            $table->string("reason");
            $table->date("date");
            $table->foreignId("issued_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("credit_notes");
    }
};
