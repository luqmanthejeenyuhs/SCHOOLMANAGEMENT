<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("class_subject_teacher", function (Blueprint $table) {
            $table->id();
            $table->foreignId("teacher_id")->constrained()->cascadeOnDelete();
            $table->foreignId("subject_id")->constrained()->cascadeOnDelete();
            $table->foreignId("school_class_id")->constrained()->cascadeOnDelete();
            $table->foreignId("section_id")->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("class_subject_teacher");
    }
};
