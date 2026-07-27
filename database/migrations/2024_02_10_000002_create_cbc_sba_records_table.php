<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_sba_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->foreignId("cbc_learning_area_id")->nullable()->constrained()->nullOnDelete();
            $table->string("term");
            // Grades 4-6 sit 3 SBA performance tasks per exit profile, each worth
            // 20% (60% total) alongside the 40% KPSEA summative exam.
            $table->unsignedTinyInteger("sba_number")->comment("1, 2, or 3");
            $table->decimal("score", 5, 2);
            $table->decimal("max_score", 5, 2)->default(100);
            $table->text("remarks")->nullable();
            $table->foreignId("recorded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->unique(["student_id", "cbc_learning_area_id", "term", "sba_number"], "sba_record_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_sba_records");
    }
};
