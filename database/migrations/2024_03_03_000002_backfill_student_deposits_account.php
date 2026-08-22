<?php

use App\Models\Account;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Account::defaultChart() now includes "2000 Student Deposits / Prepaid
     * Fees", but schools whose Chart of Accounts was already seeded before
     * this account existed won't have it. This adds just that one missing
     * account, safely (firstOrCreate — won't duplicate or touch existing
     * accounts) for every school.
     */
    public function up(): void
    {
        School::all()->each(fn (School $school) => Account::seedDefaultChart($school));
    }

    public function down(): void
    {
        // Left as a no-op — see the equivalent original chart backfill
        // migration for why (may already have journal lines posted against it).
    }
};
