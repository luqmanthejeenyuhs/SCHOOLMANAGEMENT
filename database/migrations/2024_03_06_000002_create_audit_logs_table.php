<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A simple, append-only audit trail. Primarily populated by
     * App\Listeners\LogAuthenticationActivity (login success/failure,
     * logout) but App\Services\AuditLogger::log() can be called from
     * anywhere that wants to record "who did what, from where".
     *
     * school_id and user_id are both nullable: a failed login attempt with
     * an unrecognised username has no user, and a super_admin acting at the
     * platform level (or a failed attempt on the generic /login page) has
     * no school.
     */
    public function up(): void
    {
        Schema::create("audit_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->nullable()->constrained("schools")->nullOnDelete();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("event", 100);
            $table->string("username_attempted")->nullable();
            $table->string("description", 500)->nullable();
            $table->string("ip_address", 45)->nullable();
            $table->string("user_agent", 500)->nullable();
            $table->string("url", 500)->nullable();
            $table->string("method", 10)->nullable();
            $table->json("meta")->nullable();
            $table->timestamp("created_at")->nullable();

            $table->index(["school_id", "created_at"]);
            $table->index(["user_id", "created_at"]);
            $table->index("event");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("audit_logs");
    }
};
