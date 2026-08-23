<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("leave_requests", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->enum("leave_type", ["annual", "sick", "maternity", "paternity", "compassionate", "unpaid", "other"]);
            $table->date("start_date");
            $table->date("end_date");
            $table->unsignedInteger("days");
            $table->text("reason")->nullable();
            $table->enum("status", ["pending", "approved", "rejected"])->default("pending");
            $table->foreignId("reviewed_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamp("reviewed_at")->nullable();
            $table->string("review_note")->nullable();
            $table->timestamps();

            $table->index(["school_id", "employee_id", "status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("leave_requests");
    }
};
