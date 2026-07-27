<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("employees", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("teacher_id")->nullable()->constrained()->nullOnDelete();
            $table->string("name");
            $table->string("job_title");
            $table->boolean("is_teaching_staff")->default(false);
            $table->string("id_number")->nullable();
            $table->string("kra_pin")->nullable();
            $table->string("nssf_number")->nullable();
            $table->string("shif_number")->nullable();
            $table->string("phone")->nullable();
            $table->decimal("basic_salary", 10, 2)->default(0);
            $table->decimal("house_allowance", 10, 2)->default(0);
            $table->decimal("transport_allowance", 10, 2)->default(0);
            $table->decimal("other_allowances", 10, 2)->default(0);
            $table->date("employment_date")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("employees");
    }
};
