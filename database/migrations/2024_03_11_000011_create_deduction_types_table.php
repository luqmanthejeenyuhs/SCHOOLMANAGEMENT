<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded: this table may already exist under an earlier
        // migration filename from a separately-delivered update
        // package — renaming files to avoid a collision doesn't
        // change what Laravel tracks by filename, so without this
        // check a rename alone would try to re-create an existing table.
        if (Schema::hasTable("deduction_types")) {
            return;
        }

        Schema::create("deduction_types", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            // Statutory rows (PAYE, NSSF, SHIF, Housing Levy) are seeded
            // automatically as read-only reference entries — they're
            // computed by PayrollService, never manually assigned. Only
            // non-statutory rows (SACCO, union dues, etc.) can be assigned
            // to employees.
            $table->boolean("is_statutory")->default(false);
            $table->boolean("is_percentage")->default(false);
            $table->decimal("rate_or_amount", 10, 2)->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("deduction_types");
    }
};
