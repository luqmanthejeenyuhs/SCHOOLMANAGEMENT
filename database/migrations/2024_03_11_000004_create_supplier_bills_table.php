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
        if (Schema::hasTable("supplier_bills")) {
            return;
        }

        Schema::create("supplier_bills", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            $table->string("bill_reference")->nullable();
            $table->string("description");
            // Amount excluding VAT; vat_amount and total_amount are derived
            // and stored (not computed on the fly) so a later change to the
            // standard VAT rate never silently rewrites the value of a bill
            // that was already posted to the ledger.
            $table->decimal("amount", 12, 2);
            $table->decimal("vat_rate", 5, 2)->default(0);
            $table->decimal("vat_amount", 12, 2)->default(0);
            $table->decimal("total_amount", 12, 2);
            $table->date("bill_date");
            $table->date("due_date");
            $table->enum("status", ["unpaid", "partially_paid", "paid"])->default("unpaid");
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("supplier_bills");
    }
};
