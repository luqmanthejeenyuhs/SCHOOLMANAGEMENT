<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("exam_results", function (Blueprint $table) {
            $table->id();
            $table->foreignId("exam_id")->constrained()->cascadeOnDelete();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->foreignId("subject_id")->constrained()->cascadeOnDelete();
            $table->decimal("marks_obtained", 5, 2)->default(0);
            $table->decimal("max_marks", 5, 2)->default(100);
            $table->string("grade")->nullable();
            $table->timestamps();

            $table->unique(["exam_id", "student_id", "subject_id"], "exam_student_subject_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("exam_results");
    }
};
