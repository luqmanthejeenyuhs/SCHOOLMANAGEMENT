<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("students", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string("admission_no")->unique();
            $table->foreignId("school_class_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("section_id")->nullable()->constrained()->nullOnDelete();
            $table->string("guardian_name")->nullable();
            $table->string("guardian_phone")->nullable();
            $table->date("dob")->nullable();
            $table->string("address")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("students");
    }
};
