<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("exam_comments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("exam_id")->constrained()->cascadeOnDelete();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->text("class_teacher_comment")->nullable();
            $table->text("principal_comment")->nullable();
            $table->foreignId("recorded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->unique(["exam_id", "student_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("exam_comments");
    }
};
