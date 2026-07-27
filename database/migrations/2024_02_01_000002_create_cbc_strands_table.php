<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_strands", function (Blueprint $table) {
            $table->id();
            $table->foreignId("cbc_learning_area_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_strands");
    }
};
