<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("grading_scales", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->string("grade");
            $table->decimal("min_score", 5, 2);
            $table->decimal("max_score", 5, 2);
            $table->decimal("points", 3, 1)->nullable();
            $table->string("remark")->nullable();
            $table->timestamps();
        });

        // NOTE: grading scales are now per-school, so there's no global default
        // to insert here (no school exists yet at migration time). Every new
        // school gets a sensible 8-4-4 style default scale automatically —
        // see App\Observers\SchoolObserver::created().
    }

    public function down(): void
    {
        Schema::dropIfExists("grading_scales");
    }
};
