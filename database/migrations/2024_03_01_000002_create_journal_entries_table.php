<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("journal_entries", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->date("date");
            $table->string("memo");
            $table->string("reference")->nullable();
            // "manual" for entries typed in via the Journal Entries screen,
            // or the model class that auto-posted this (e.g. "payment") —
            // lets the Ledger/Journal screens show where an entry came from
            // and stops anyone accidentally editing an auto-posted one.
            $table->string("source_type")->default("manual");
            $table->unsignedBigInteger("source_id")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["source_type", "source_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("journal_entries");
    }
};
