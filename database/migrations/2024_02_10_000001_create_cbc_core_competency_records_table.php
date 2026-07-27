<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_core_competency_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->enum("competency", [
                "communication_and_collaboration",
                "critical_thinking_and_problem_solving",
                "creativity_and_imagination",
                "citizenship",
                "digital_literacy",
                "learning_to_learn",
                "self_efficacy",
            ]);
            $table->string("term");
            $table->enum("performance_level", ["EE", "ME", "AE", "BE"]);
            $table->text("remarks")->nullable();
            $table->foreignId("recorded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->unique(["student_id", "competency", "term"], "core_competency_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_core_competency_records");
    }
};
