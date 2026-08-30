<?php

use App\Models\Account;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // firstOrCreate on code means this only adds the NEW accounts to
        // schools that already have the original chart — nothing existing
        // is touched or duplicated.
        School::all()->each(fn (School $school) => Account::seedDefaultChart($school));
    }

    public function down(): void
    {
        // Left as a no-op deliberately — see the original chart backfill
        // migration for why (accounts may already have journal lines).
    }
};
