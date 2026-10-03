<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which fee category a class belongs to (e.g. Grade 2 -> Lower
     * Primary). Nullable — a class with no category assigned just doesn't
     * show up when generating invoices "by category", same as before this
     * feature existed.
     */
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('fee_category_id')->nullable()->after('school_id')
                ->constrained('fee_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fee_category_id');
        });
    }
};
