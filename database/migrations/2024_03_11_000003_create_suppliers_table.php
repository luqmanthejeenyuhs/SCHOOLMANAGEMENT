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
        if (Schema::hasTable("suppliers")) {
            return;
        }

        Schema::create("suppliers", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->string("contact_person")->nullable();
            $table->string("phone")->nullable();
            $table->string("email")->nullable();
            $table->string("kra_pin")->nullable();
            $table->text("address")->nullable();
            // How many days after a bill date this supplier's terms give the
            // school to pay — "buying on credit" from them. Defaults to a
            // common 30-day trade credit term.
            $table->unsignedSmallInteger("payment_terms_days")->default(30);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("suppliers");
    }
};
