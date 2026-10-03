<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A fee type scoped to a category (e.g. "Tuition — Lower Primary" at
     * KES 8,000) only applies to that category's classes. Left null, a
     * fee type is school-wide, exactly like before this feature existed —
     * fully backward compatible with every fee type created before now.
     */
    public function up(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->foreignId('fee_category_id')->nullable()->after('school_id')
                ->constrained('fee_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fee_category_id');
        });
    }
};
