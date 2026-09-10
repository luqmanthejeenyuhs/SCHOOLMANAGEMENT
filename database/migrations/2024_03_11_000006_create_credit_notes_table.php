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
        if (Schema::hasTable("credit_notes")) {
            return;
        }

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
