<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_value_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            // The 7 KICD-defined national values embedded in the CBC.
            $table->enum("value_area", [
                "love", "responsibility", "respect", "unity", "peace", "patriotism", "integrity",
            ]);
            $table->string("term");
            $table->enum("rating", ["EE", "ME", "AE", "BE"]);
            $table->text("remarks")->nullable();
            $table->foreignId("recorded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->unique(["student_id", "value_area", "term"], "value_record_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_value_records");
    }
};
