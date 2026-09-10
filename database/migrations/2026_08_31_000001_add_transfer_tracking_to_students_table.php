<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Minimal transfer tracking, used by the class "Details" tab to show
     * transfers in/out for a class. Deliberately simple: a status flag
     * (rather than a full transfer history log) — good enough to answer
     * "how many transferred in/out", not a full audit trail of moves.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('status', ['active', 'transferred_out'])->default('active')->after('school_level');
            $table->boolean('admitted_via_transfer')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['status', 'admitted_via_transfer']);
        });
    }
};
