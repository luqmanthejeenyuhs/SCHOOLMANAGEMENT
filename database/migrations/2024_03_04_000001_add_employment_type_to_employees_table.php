<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("employees", function (Blueprint $table) {
            // Classifies the employment relationship, separate from
            // is_teaching_staff (which is about WHAT they do, not the
            // terms). Defaults to full_time only for existing rows being
            // backfilled by this migration — every new Employee record
            // going forward requires an explicit choice on the form (see
            // Admin\TeacherController and Admin\EmployeeController), so
            // nobody gets silently defaulted into the wrong classification.
            $table->enum("employment_type", ["full_time", "part_time", "contract", "intern", "volunteer"])
                ->default("full_time")
                ->after("job_title");
        });
    }

    public function down(): void
    {
        Schema::table("employees", function (Blueprint $table) {
            $table->dropColumn("employment_type");
        });
    }
};
