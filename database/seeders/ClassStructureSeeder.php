<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\Facades\Tenant;
use Illuminate\Database\Seeder;

/**
 * Ensures Grade 1 - Grade 9 exist, each with streams A, B, C. Safe to
 * re-run — uses firstOrCreate, so it only adds what's missing and never
 * duplicates or deletes a class/section you already have.
 *
 * Any student whose admission_no starts with "DEMO-" (i.e. created by
 * BulkDemoDataSeeder) is re-spread round-robin across this Grade 1-9
 * structure, so your test population lands somewhere sensible. Real
 * students (ADM- admission numbers) are left untouched.
 *
 * Every section that doesn't already have a class_teacher_id is assigned
 * one, cycling through your demo teachers (demo.teacherN@school.test) —
 * a section that already has a class teacher is left alone.
 *
 * Run with:
 *   php artisan db:seed --class="Database\Seeders\ClassStructureSeeder"
 */
class ClassStructureSeeder extends Seeder
{
    // Change this if your test data should go into a different school.
    protected int $schoolId = 1;

    protected array $grades = [
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
        'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9',
    ];

    protected array $streams = ['A', 'B', 'C'];

    public function run(): void
    {
        $school = School::find($this->schoolId);

        if (! $school) {
            $this->command->error("No school found with id {$this->schoolId}. Edit \$schoolId in ClassStructureSeeder.");

            return;
        }

        Tenant::runFor($school->id, function () use ($school) {
            $classes = $this->ensureGrades();
            $reassigned = $this->respreadDemoStudents($classes);
            $teacherAssignments = $this->assignClassTeachers($classes);

            $this->command->info('');
            $this->command->info("Grade 1 - Grade 9 (streams A/B/C) are set up for {$school->name}.");
            $this->command->info("Re-spread {$reassigned} demo student(s) across the new structure.");
            $this->command->info("Assigned a class teacher to {$teacherAssignments} section(s).");
        });
    }

    protected function ensureGrades()
    {
        $classes = collect();

        foreach ($this->grades as $name) {
            $class = SchoolClass::firstOrCreate(['name' => $name]);

            foreach ($this->streams as $stream) {
                Section::firstOrCreate([
                    'school_class_id' => $class->id,
                    'name' => $stream,
                ]);
            }

            $classes->push($class->load('sections'));
        }

        return $classes;
    }

    protected function respreadDemoStudents($classes): int
    {
        $students = Student::where('admission_no', 'like', 'DEMO-%')->get();
        $count = 0;

        foreach ($students as $i => $student) {
            $class = $classes[$i % $classes->count()];
            $section = $class->sections[$i % $class->sections->count()];

            $student->update([
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'school_level' => 'junior',
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Assigns a class teacher to every section that doesn't already have
     * one, cycling through demo teachers (demo.teacherN@school.test). If
     * there aren't enough demo teachers to cover every section, remaining
     * sections are left without a class teacher rather than reusing one
     * teacher for two sections at once — run BulkDemoDataSeeder first (or
     * raise its teacher count) if you want full coverage.
     */
    protected function assignClassTeachers($classes): int
    {
        $teachers = Teacher::whereHas('user', fn ($q) => $q->where('email', 'like', 'demo.teacher%@school.test'))
            ->with('user')
            ->get()
            ->sortBy(fn ($t) => (int) filter_var($t->user->email, FILTER_SANITIZE_NUMBER_INT))
            ->values();

        if ($teachers->isEmpty()) {
            $this->command->warn('No demo teachers found — run BulkDemoDataSeeder first if you want sections to get class teachers.');

            return 0;
        }

        $sections = $classes->flatMap(fn ($class) => $class->sections)->values();
        $count = 0;
        $teacherIndex = 0;

        foreach ($sections as $section) {
            if ($section->class_teacher_id) {
                continue;
            }

            if ($teacherIndex >= $teachers->count()) {
                break;
            }

            $section->update(['class_teacher_id' => $teachers[$teacherIndex]->id]);
            $teacherIndex++;
            $count++;
        }

        return $count;
    }
}
