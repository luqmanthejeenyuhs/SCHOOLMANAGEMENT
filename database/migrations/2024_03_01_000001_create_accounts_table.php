<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("accounts", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained()->cascadeOnDelete();
            $table->string("code", 20);
            $table->string("name");
            // asset | liability | equity | income | expense — determines which
            // side (debit or credit) counts as a normal/increasing balance.
            $table->string("type");
            // System accounts (Cash, Bank, M-Pesa, Fees Income) are what
            // automatic posting (e.g. a fee payment) targets by code — protect
            // them from being deleted from the Chart of Accounts screen.
            $table->boolean("is_system")->default(false);
            $table->timestamps();

            $table->unique(["school_id", "code"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("accounts");
    }
};
