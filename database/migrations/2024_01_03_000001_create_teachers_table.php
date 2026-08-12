<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("teachers", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string("employee_id");
            $table->string("qualification")->nullable();
            $table->string("address")->nullable();
            $table->date("joining_date")->nullable();
            $table->timestamps();

            $table->unique(["school_id", "employee_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("teachers");
    }
};
