<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // MySQL enums can't be altered with Schema::table(), so we modify the column directly.
    DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('super_admin', 'admin', 'teacher', 'student', 'parent') NOT NULL DEFAULT 'student'");
}

public function down(): void
{
    DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('super_admin', 'admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
}
};
