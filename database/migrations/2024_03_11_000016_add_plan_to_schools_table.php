<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn("schools", "plan")) {
            return;
        }

        Schema::table("schools", function (Blueprint $table) {
            $table->string("plan")->default("trial")->after("slug");
        });
    }

    public function down(): void
    {
        Schema::table("schools", function (Blueprint $table) {
            $table->dropColumn("plan");
        });
    }
};
