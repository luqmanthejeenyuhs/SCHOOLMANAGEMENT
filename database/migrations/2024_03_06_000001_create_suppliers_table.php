<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
