<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("payslips", function (Blueprint $table) {
            $table->unsignedTinyInteger("unpaid_leave_days")->default(0)->after("other_deductions");
        });
    }

    public function down(): void
    {
        Schema::table("payslips", function (Blueprint $table) {
            $table->dropColumn("unpaid_leave_days");
        });
    }
};
