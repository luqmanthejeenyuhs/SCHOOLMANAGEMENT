<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("sms_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_id")->nullable()->constrained()->nullOnDelete();
            $table->string("recipient_phone");
            $table->text("message");
            $table->string("category")->default("general"); // fee_reminder, receipt, announcement, closure, attendance
            $table->enum("status", ["queued", "sent", "failed"])->default("queued");
            $table->text("provider_response")->nullable();
            $table->foreignId("sent_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sms_logs");
    }
};
