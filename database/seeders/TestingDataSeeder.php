<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Adds test/demo data on top of whatever is already in the database, without
 * wiping anything: a sample teacher (Victor) assigned to Agriculture, Maths
 * and English across Grade 9 & 10, plus two weeks of varied attendance
 * history for every existing student so the attendance screens have
 * something realistic to show (mix of present/absent/late/excused).
 *
 * Safe to re-run — everything uses firstOrCreate/updateOrCreate.
 *
 * Run with: php artisan db:seed --class=TestingDataSeeder
 */
class TestingDataSeeder extends Seeder
{
    public function run(): void
    {
        $grade9 = SchoolClass::where("name", "Grade 9")->first();
        $grade10 = SchoolClass::where("name", "Grade 10")->first();

        if (! $grade9 || ! $grade10) {
            $this->command->warn("Grade 9 / Grade 10 not found — run the main DatabaseSeeder first (php artisan db:seed).");

            return;
        }

        $g9A = Section::where("school_class_id", $grade9->id)->first();
        $g10A = Section::where("school_class_id", $grade10->id)->first();

        // --- Subjects Victor will teach (created only if missing) ---
        $agriG9 = Subject::firstOrCreate(["school_class_id" => $grade9->id, "name" => "Agriculture"], ["code" => "AGR9"]);
        $agriG10 = Subject::firstOrCreate(["school_class_id" => $grade10->id, "name" => "Agriculture"], ["code" => "AGR10"]);
        $engG10 = Subject::firstOrCreate(["school_class_id" => $grade10->id, "name" => "English"], ["code" => "ENG10"]);
        $mathG9 = Subject::where("school_class_id", $grade9->id)->where("name", "Mathematics")->first();
        $engG9 = Subject::where("school_class_id", $grade9->id)->where("name", "English")->first();

        // --- Teacher Victor ---
        $victorUser = User::firstOrCreate(
            ["email" => "victor.teacher@school.test"],
            ["name" => "Victor Kimani", "password" => Hash::make("password"), "role" => "teacher"]
        );

        $victor = Teacher::firstOrCreate(
            ["user_id" => $victorUser->id],
            [
                "employee_id" => "TEMP-".uniqid(),
                "qualification" => "B.Ed Agriculture",
                "joining_date" => now()->subYears(2),
            ]
        );

        if (str_starts_with((string) $victor->employee_id, "TEMP-")) {
            $victor->update(["employee_id" => "EMPLOYEE-".$victor->id]);
        }

        $assign = function ($subject, SchoolClass $class, ?Section $section) use ($victor) {
            if (! $subject) {
                return;
            }

            ClassSubjectTeacher::firstOrCreate([
                "teacher_id" => $victor->id,
                "subject_id" => $subject->id,
                "school_class_id" => $class->id,
                "section_id" => $section?->id,
            ]);
        };

        $assign($agriG9, $grade9, $g9A);
        $assign($agriG10, $grade10, $g10A);
        $assign($mathG9, $grade9, $g9A);
        $assign($engG9, $grade9, $g9A);
        $assign($engG10, $grade10, $g10A);

        // --- Two weeks of varied attendance for every existing student ---
        // Weighted toward "present" so it still looks like a normal register,
        // but with enough absent/late/excused mixed in to be useful for testing.
        $statuses = ["present", "present", "present", "present", "present", "present", "present", "absent", "late", "excused"];

        $students = Student::all();

        foreach ($students as $student) {
            for ($d = 13; $d >= 0; $d--) {
                Attendance::updateOrCreate(
                    ["student_id" => $student->id, "date" => now()->subDays($d)->toDateString()],
                    ["status" => $statuses[array_rand($statuses)], "marked_by" => $victorUser->id]
                );
            }
        }

        $this->command->info("Testing data seeded: teacher Victor Kimani (Agriculture, Maths, English) + 14 days of varied attendance for every student. Login: victor.teacher@school.test / password");
    }
}
