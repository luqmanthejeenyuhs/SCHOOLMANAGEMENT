<?php

use App\Models\DeductionType;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        School::all()->each(fn (School $school) => DeductionType::seedStatutoryReference($school));
    }

    public function down(): void
    {
        // No-op — see the account chart backfill migration for why.
    }
};
