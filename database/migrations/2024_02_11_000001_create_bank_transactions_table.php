<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('bank_reference')->unique()->comment('Unique transaction reference from the bank feed (e.g. Equity Jenga, Co-op notification)');
            $table->string('account_reference')->comment('What the depositor typed in as reference — expected to be the student admission number');
            $table->decimal('amount', 12, 2);
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['matched', 'unmatched'])->default('unmatched');
            $table->json('raw_payload')->nullable();
            $table->timestamp('deposited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
