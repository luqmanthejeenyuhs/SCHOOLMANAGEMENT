<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("grading_scales", function (Blueprint $table) {
            $table->id();
            $table->string("grade");
            $table->decimal("min_score", 5, 2);
            $table->decimal("max_score", 5, 2);
            $table->decimal("points", 3, 1)->nullable();
            $table->string("remark")->nullable();
            $table->timestamps();
        });

        // Sensible 8-4-4 style default so the exam module works immediately;
        // admins can edit/replace these from Grading Scale settings.
        DB::table("grading_scales")->insert([
            ["grade" => "A", "min_score" => 80, "max_score" => 100, "points" => 12, "remark" => "Excellent", "created_at" => now(), "updated_at" => now()],
            ["grade" => "B", "min_score" => 70, "max_score" => 79.99, "points" => 9, "remark" => "Good", "created_at" => now(), "updated_at" => now()],
            ["grade" => "C", "min_score" => 60, "max_score" => 69.99, "points" => 6, "remark" => "Average", "created_at" => now(), "updated_at" => now()],
            ["grade" => "D", "min_score" => 50, "max_score" => 59.99, "points" => 3, "remark" => "Below Average", "created_at" => now(), "updated_at" => now()],
            ["grade" => "E", "min_score" => 0, "max_score" => 49.99, "points" => 1, "remark" => "Needs Improvement", "created_at" => now(), "updated_at" => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists("grading_scales");
    }
};
