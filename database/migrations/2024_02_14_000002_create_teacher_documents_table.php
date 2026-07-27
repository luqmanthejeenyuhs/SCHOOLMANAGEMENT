<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("teacher_documents", function (Blueprint $table) {
            $table->id();
            $table->foreignId("teacher_id")->constrained()->cascadeOnDelete();
            $table->string("type"); // passport_photo, national_id_document, police_clearance, other
            $table->string("original_name");
            $table->string("path");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("teacher_documents");
    }
};
