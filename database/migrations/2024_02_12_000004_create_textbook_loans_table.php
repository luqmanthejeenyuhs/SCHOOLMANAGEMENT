<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textbook_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textbook_copy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->date('due_date')->nullable();
            $table->string('condition_at_issue')->default('good');
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_at_return')->nullable();
            $table->enum('status', ['issued', 'returned', 'lost', 'damaged'])->default('issued');
            $table->foreignId('penalty_invoice_id')->nullable()->constrained('fee_invoices')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textbook_loans');
    }
};
