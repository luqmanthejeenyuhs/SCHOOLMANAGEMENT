<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("exams", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId("school_class_id")->constrained()->cascadeOnDelete();
            $table->string("term")->nullable();
            $table->date("exam_date")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("exams");
    }
};
