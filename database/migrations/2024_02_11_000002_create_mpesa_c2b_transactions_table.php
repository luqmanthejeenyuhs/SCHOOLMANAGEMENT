<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_c2b_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique()->comment('Safaricom TransID from the C2B confirmation payload');
            $table->string('msisdn')->comment('Payer phone number (MSISDN)');
            $table->string('bill_ref_number')->comment('Account number the payer typed at the Paybill menu — expected to be the student admission number');
            $table->decimal('amount', 12, 2);
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['matched', 'unmatched'])->default('unmatched');
            $table->json('raw_payload')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_c2b_transactions');
    }
};
