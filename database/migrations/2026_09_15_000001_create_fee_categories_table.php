<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A "Fee Category" groups classes that share the same fee schedule —
     * e.g. "Pre-Primary" (PP1-PP2), "Lower Primary" (Grade 1-3), "Upper
     * Primary" (Grade 4-6), "Junior Secondary" (Grade 7-9). Every school
     * defines its own — see Admin\FeeCategoryController.
     */
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};
