<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("timetable_slots", function (Blueprint $table) {
            $table->id();
            // Which stream (e.g. Grade 9 Green) this lesson is for.
            $table->foreignId("section_id")->constrained()->cascadeOnDelete();
            $table->foreignId("subject_id")->constrained()->cascadeOnDelete();
            $table->foreignId("teacher_id")->constrained()->cascadeOnDelete();
            $table->string("day_of_week"); // Monday..Saturday
            $table->time("start_time");
            $table->time("end_time");
            $table->string("room")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("timetable_slots");
    }
};
