<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("students", function (Blueprint $table) {
            $table->enum("school_level", ["junior", "senior"])->default("junior")->after("section_id");
            $table->string("pathway")->nullable()->after("school_level")->comment("Senior School CBC pathway: STEM, Arts & Sports Science, Social Sciences");
            $table->string("upi_number")->nullable()->after("admission_no")->comment("NEMIS Unique Personal Identifier");
            $table->string("assessment_number")->nullable()->after("upi_number")->comment("KNEC KPSEA/KJSEA assessment number");
        });
    }

    public function down(): void
    {
        Schema::table("students", function (Blueprint $table) {
            $table->dropColumn(["school_level", "pathway", "upi_number", "assessment_number"]);
        });
    }
};
