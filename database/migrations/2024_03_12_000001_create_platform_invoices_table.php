<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a SCHOOL owes the PLATFORM for using Taaluma SMS itself — a
     * completely different thing from FeeInvoice (what a STUDENT owes
     * THEIR school). Only ever created/viewed by a super_admin; deliberately
     * NOT tenant-scoped (no BelongsToTenant on the model) since a school
     * admin has no business seeing or being scoped into this table at all.
     */
    public function up(): void
    {
        Schema::create("platform_invoices", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount", 12, 2);
            $table->string("billing_cycle", 20)->default("monthly");
            $table->date("due_date");
            $table->string("status", 20)->default("pending");
            $table->timestamp("paid_at")->nullable();
            $table->string("paid_method", 50)->nullable();
            $table->string("note", 500)->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["school_id", "status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("platform_invoices");
    }
};
