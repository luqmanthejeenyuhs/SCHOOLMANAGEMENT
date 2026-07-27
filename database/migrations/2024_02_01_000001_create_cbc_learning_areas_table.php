<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_learning_areas", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->enum("school_level", ["junior", "senior"])->default("junior");
            $table->string("pathway")->nullable(); // e.g. STEM, Arts & Sports Science, Social Sciences (senior school only)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_learning_areas");
    }
};
