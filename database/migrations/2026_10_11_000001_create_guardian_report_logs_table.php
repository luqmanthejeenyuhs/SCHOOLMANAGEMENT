<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("guardian_report_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("student_id")->nullable()->constrained()->nullOnDelete();
            $table->string("channel", 10);                 // sms | email
            $table->string("recipient");                   // phone number or email address
            $table->string("recipient_name")->nullable();
            $table->string("report_types");                // e.g. "attendance,results,fees"
            $table->string("subject")->nullable();         // email subject / SMS summary
            $table->string("status", 10)->default("sent"); // sent | queued | failed
            $table->text("error")->nullable();
            $table->foreignId("sent_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["school_id", "created_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("guardian_report_logs");
    }
};
