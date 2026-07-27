<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cbc_portfolio_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->constrained()->cascadeOnDelete();
            $table->foreignId("cbc_sub_strand_id")->nullable()->constrained()->nullOnDelete();
            $table->string("title");
            $table->enum("evidence_type", ["photo", "document", "audio", "video", "other"])->default("photo");
            $table->string("file_path");
            $table->string("original_filename")->nullable();
            $table->string("term")->nullable();
            $table->text("notes")->nullable();
            $table->foreignId("uploaded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cbc_portfolio_items");
    }
};
