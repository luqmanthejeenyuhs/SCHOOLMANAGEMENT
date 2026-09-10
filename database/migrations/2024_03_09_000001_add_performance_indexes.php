<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These four tables grow the fastest of anything in the schema —
     * attendance and payments accumulate daily, exam results every exam
     * sitting — and every existing index on them happens to start with a
     * column that doesn't match the app's actual hot queries:
     *
     *  - attendances only had a unique index starting with student_id, but
     *    the admin dashboard queries "how many present/absent today/this
     *    week" across the WHOLE SCHOOL by date, not by student.
     *  - exam_results only had a unique index starting with exam_id, but
     *    the student portal's Results/Performance pages fetch "all of
     *    THIS STUDENT's results", not "everyone's results for one exam".
     *  - fee_invoices had no index touching `status` at all, which the
     *    dashboard's "unpaid invoices" count filters on directly.
     *  - payments had no index on `payment_date`, which the dashboard's
     *    "collected this month" figure filters on directly.
     *
     * None of this matters yet with a handful of students, but it's the
     * difference between a query that stays fast forever and one that
     * quietly gets slower every term as a school's history accumulates —
     * cheap to add now, expensive to add later once tables are huge and
     * an index build locks things up.
     */
    public function up(): void
    {
        Schema::table("attendances", function (Blueprint $table) {
            $table->index(["school_id", "date", "status"], "attendances_school_date_status_idx");
        });

        Schema::table("exam_results", function (Blueprint $table) {
            $table->index(["school_id", "student_id"], "exam_results_school_student_idx");
        });

        Schema::table("fee_invoices", function (Blueprint $table) {
            $table->index(["school_id", "status"], "fee_invoices_school_status_idx");
        });

        Schema::table("payments", function (Blueprint $table) {
            $table->index(["school_id", "payment_date"], "payments_school_date_idx");
        });
    }

    /**
     * Note: rolling this back can fail with "needed in a foreign key
     * constraint" if MySQL decided one of these composite indexes is the
     * only thing currently covering its leading school_id column — this
     * doesn't affect the normal forward `migrate --force` deploy path at
     * all, only an explicit `migrate:rollback` of this specific migration.
     * If that ever comes up, drop the FK constraint first, then the
     * index, then re-add the constraint (which recreates its own index).
     */
    public function down(): void
    {
        Schema::table("attendances", function (Blueprint $table) {
            $table->dropIndex("attendances_school_date_status_idx");
        });

        Schema::table("exam_results", function (Blueprint $table) {
            $table->dropIndex("exam_results_school_student_idx");
        });

        Schema::table("fee_invoices", function (Blueprint $table) {
            $table->dropIndex("fee_invoices_school_status_idx");
        });

        Schema::table("payments", function (Blueprint $table) {
            $table->dropIndex("payments_school_date_idx");
        });
    }
};
