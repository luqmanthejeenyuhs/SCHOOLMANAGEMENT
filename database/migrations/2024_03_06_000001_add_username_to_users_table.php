<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Adds a `username` column used for sign-in instead of email (see
     * App\Http\Controllers\Auth\LoginController). Usernames are unique
     * *per school* (not globally) — two different schools can each have a
     * "jsmith" — so the unique index is composite on (school_id, username).
     *
     * We deliberately leave the column nullable at the database level
     * (this environment has no doctrine/dbal, so a later ->change() to
     * NOT NULL isn't available) and enforce "required" in validation
     * instead. Existing users are backfilled here so nobody is locked out.
     */
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string("username")->nullable()->after("email");
        });

        Schema::table("users", function (Blueprint $table) {
            $table->unique(["school_id", "username"]);
        });

        $this->backfillUsernames();
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropUnique(["school_id", "username"]);
            $table->dropColumn("username");
        });
    }

    /**
     * Derive a starting username from each existing user's email
     * (the part before the @), then de-duplicate within their school by
     * appending a number if needed.
     */
    protected function backfillUsernames(): void
    {
        $users = DB::table("users")->select("id", "school_id", "email", "name")->orderBy("id")->get();

        $taken = [];

        foreach ($users as $user) {
            $base = Str::of($user->email ?? $user->name ?? ("user".$user->id))
                ->before("@")
                ->lower()
                ->replaceMatches("/[^a-z0-9._-]+/", "")
                ->trim("._-");

            $base = (string) $base;
            if ($base === "") {
                $base = "user".$user->id;
            }

            $schoolKey = $user->school_id ?? "null";
            $taken[$schoolKey] ??= [];

            $candidate = $base;
            $suffix = 1;
            while (in_array($candidate, $taken[$schoolKey], true)) {
                $candidate = $base."".(++$suffix);
            }

            $taken[$schoolKey][] = $candidate;

            DB::table("users")->where("id", $user->id)->update(["username" => $candidate]);
        }
    }
};
