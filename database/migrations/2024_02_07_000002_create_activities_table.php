<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("activities", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            // Teacher in charge of the activity (e.g. the swimming coach).
            $table->foreignId("patron_id")->nullable()->constrained("teachers")->nullOnDelete();
            // "Daily" or a specific weekday name (Monday, Tuesday, ...).
            $table->string("day_of_week")->nullable();
            $table->time("start_time")->nullable();
            $table->time("end_time")->nullable();
            $table->string("venue")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("activities");
    }
};
