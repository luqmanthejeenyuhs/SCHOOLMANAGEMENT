<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_competency_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->foreignId("cbc_sub_strand_id")->constrained()->cascadeOnDelete();
            $table->string("term"); // e.g. "Term 1 2026"
            // MoE 4-level CBC rating scale
            $table->enum("performance_level", ["EE", "ME", "AE", "BE"])->comment("EE=Exceeding Expectation, ME=Meeting Expectation, AE=Approaching Expectation, BE=Below Expectation");
            $table->text("remarks")->nullable();
            $table->foreignId("recorded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->unique(["student_id", "cbc_sub_strand_id", "term"], "cbc_record_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_competency_records");
    }
};
