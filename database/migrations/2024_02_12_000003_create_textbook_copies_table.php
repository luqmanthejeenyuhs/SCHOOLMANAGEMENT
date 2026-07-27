<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textbook_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->string('barcode')->unique();
            $table->enum('condition', ['good', 'fair', 'damaged', 'lost'])->default('good');
            $table->enum('status', ['in_store', 'issued'])->default('in_store');
            $table->foreignId('current_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textbook_copies');
    }
};
