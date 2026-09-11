<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A banner a super_admin can broadcast to every school (or a chosen
     * few) — "scheduled maintenance Sunday", "new feature available",
     * that sort of thing. Not tenant-scoped; audience is either "all" or
     * an explicit list of school IDs in school_ids.
     */
    public function up(): void
    {
        Schema::create("announcements", function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->text("body");
            $table->string("audience", 20)->default("all"); // all | specific
            $table->json("school_ids")->nullable(); // only used when audience = specific
            $table->string("level", 20)->default("info"); // info | warning | success
            $table->boolean("is_active")->default(true);
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("announcements");
    }
};
