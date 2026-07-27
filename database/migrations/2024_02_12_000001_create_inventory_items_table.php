<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->enum('category', ['consumable', 'textbook'])->default('consumable');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity_in_stock')->default(0);
            $table->unsignedInteger('reorder_level')->default(5);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
