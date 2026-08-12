<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The full catalogue of assignable rights, grouped by category (tab).
     * Keys are stable identifiers — safe to reference in code/middleware.
     */
    public function permissions(): array
    {
        return [
            "Administrative" => [
                "manage_settings" => "Access Settings",
                "manage_rights" => "Manage User Rights",
                "manage_classes" => "Manage Classes & Sections",
                "manage_subjects" => "Manage Subjects",
                "manage_timetable" => "Manage Timetable",
                "manage_activities" => "Manage Extra-Curricular Activities",
            ],
            "Teachers" => [
                "view_teachers" => "View Teachers",
                "create_teacher" => "Add Teachers",
                "edit_teacher" => "Edit Teachers",
                "delete_teacher" => "Remove Teachers",
                "manage_teacher_documents" => "Manage Teacher Documents",
            ],
            "Students" => [
                "view_students" => "View Students",
                "create_student" => "Add Students",
                "edit_student" => "Edit Students",
                "delete_student" => "Remove Students",
            ],
            "Parents" => [
                "view_parents" => "View Parents",
                "create_parent" => "Add Parent Accounts",
                "delete_parent" => "Remove Parent Accounts",
            ],
            "Academics" => [
                "manage_exams" => "Manage Exams",
                "enter_results" => "Enter/Edit Exam Results",
                "manage_grading_scales" => "Manage Grading Scales",
                "manage_cbc" => "Manage CBC Curriculum & Assessments",
            ],
            "Finance" => [
                "manage_fee_types" => "Manage Fee Types",
                "manage_invoices" => "Manage Invoices",
                "record_payments" => "Record Payments",
                "manage_finance_ledger" => "Manage Bank & M-Pesa Ledger",
                "manage_inventory" => "Manage Inventory & Store",
                "manage_textbooks" => "Manage Textbooks",
            ],
            "HR & Payroll" => [
                "manage_employees" => "Manage Staff/Employees",
                "generate_payslips" => "Generate Payslips",
                "view_staff_attendance" => "View Staff Attendance",
            ],
            "Communication" => [
                "send_sms" => "Send Bulk SMS",
            ],
        ];
    }

    public function up(): void
    {
        $now = now();
        $rows = [];

        foreach ($this->permissions() as $category => $items) {
            foreach ($items as $key => $name) {
                $rows[] = [
                    "key" => $key,
                    "name" => $name,
                    "category" => $category,
                    "created_at" => $now,
                    "updated_at" => $now,
                ];
            }
        }

        // upsert so re-running (or a partially-seeded db) doesn't error on the unique key
        DB::table("permissions")->upsert($rows, ["key"], ["name", "category", "updated_at"]);
    }

    public function down(): void
    {
        $keys = collect($this->permissions())->flatMap(fn ($items) => array_keys($items))->all();

        DB::table("permissions")->whereIn("key", $keys)->delete();
    }
};
