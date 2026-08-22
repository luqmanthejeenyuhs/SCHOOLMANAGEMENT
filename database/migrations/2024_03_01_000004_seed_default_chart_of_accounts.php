<?php

use App\Models\Account;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * New schools get their default Chart of Accounts automatically from
     * App\Observers\SchoolObserver — this migration is just the one-time
     * backfill for schools that already existed before accounting shipped.
     */
    public function up(): void
    {
        School::all()->each(fn (School $school) => Account::seedDefaultChart($school));
    }

    public function down(): void
    {
        // Intentionally left as a no-op: rolling this back would mean
        // deleting accounts that may already have journal lines posted
        // against them. Use the Chart of Accounts screen to review instead.
    }
};
