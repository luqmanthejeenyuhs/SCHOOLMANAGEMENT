<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("teachers", function (Blueprint $table) {
            $table->string("id_number")->nullable()->after("employee_id");
            $table->string("tsc_number")->nullable()->after("id_number");
            $table->string("next_of_kin_name")->nullable()->after("address");
            $table->string("next_of_kin_phone")->nullable()->after("next_of_kin_name");
            $table->string("next_of_kin_relationship")->nullable()->after("next_of_kin_phone");
        });
    }

    public function down(): void
    {
        Schema::table("teachers", function (Blueprint $table) {
            $table->dropColumn([
                "id_number", "tsc_number", "next_of_kin_name", "next_of_kin_phone", "next_of_kin_relationship",
            ]);
        });
    }
};
