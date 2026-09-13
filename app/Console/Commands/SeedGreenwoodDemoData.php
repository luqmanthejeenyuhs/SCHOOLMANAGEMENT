<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ClassSubjectTeacher;
use App\Models\Employee;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeedGreenwoodDemoData extends Command
{
    protected $signature = 'demo:seed-greenwood {--password= : Password for every generated account} {--students-per-section=20}';

    protected $description = 'Wipes and rebuilds Greenwood Academy with a full set of realistic CBC demo data: Grade 1-9 x 3 streams, ~60 teachers, students, staff, textbooks, and one term of attendance.';

    protected array $firstNamesM = ['Brian', 'Kevin', 'Dennis', 'Peter', 'James', 'John', 'David', 'Samuel', 'Joseph', 'Daniel', 'Michael', 'Anthony', 'Patrick', 'Vincent', 'George', 'Charles', 'Francis', 'Elijah', 'Moses', 'Isaac'];
    protected array $firstNamesF = ['Mary', 'Grace', 'Faith', 'Joyce', 'Ann', 'Alice', 'Ruth', 'Esther', 'Mercy', 'Lucy', 'Jane', 'Catherine', 'Elizabeth', 'Nancy', 'Winnie', 'Purity', 'Beatrice', 'Agnes', 'Damaris', 'Caroline'];
    protected array $lastNames = ['Otieno', 'Wanjiru', 'Kamau', 'Njoroge', 'Achieng', 'Mutua', 'Kiptoo', 'Cheruiyot', 'Wafula', 'Odhiambo', 'Njeri', 'Kariuki', 'Mwangi', 'Omondi', 'Chebet', 'Wekesa', 'Barasa', 'Muthoni', 'Nyambura', 'Waweru', 'Kimani', 'Onyango', 'Adhiambo', 'Korir', 'Langat'];

    protected int $nameCounter = 0;

    /**
     * Not every school-scoped table actually has its own school_id column —
     * some (like class_subject_teacher) are only scoped indirectly through
     * a parent relationship. Rather than guess per-table, this checks the
     * real schema every time and returns either ['school_id' => X] or []
     * to merge into a create/insert array — makes every call site safe
     * regardless of that table's actual structure.
     */
    protected array $schoolIdSupport = [];

    protected function schoolIdIfSupported(string $table, int $schoolId): array
    {
        if (! array_key_exists($table, $this->schoolIdSupport)) {
            $this->schoolIdSupport[$table] = Schema::hasColumn($table, 'school_id');
        }

        return $this->schoolIdSupport[$table] ? ['school_id' => $schoolId] : [];
    }

    public function handle(): int
    {
        $school = School::where('slug', 'greenwood')->first();
        if (! $school) {
            $this->error('No school found with slug "greenwood". Nothing to do.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Password to use for every generated teacher/student/staff account (min 10 characters)');
        if (! $password || strlen($password) < 10) {
            $this->error('Password must be at least 10 characters. Pass --password=... or run interactively.');

            return self::FAILURE;
        }
        $hashedPassword = Hash::make($password);
        $studentsPerSection = (int) $this->option('students-per-section');

        DB::transaction(function () use ($school, $hashedPassword, $studentsPerSection) {
            $this->info("Wiping existing data for {$school->name}...");
            $this->wipeExisting($school);

            $this->info('Creating classes and streams (Grade 1-9 x Green/Yellow/Blue)...');
            $sections = $this->createClassesAndSections($school);

            $this->info('Creating CBC subjects...');
            $subjectsByClass = $this->createSubjects($school, $sections);

            $this->info('Creating 60 teachers...');
            $teachers = $this->createTeachers($school, $hashedPassword, 60);

            $this->info('Assigning class teachers and subject teachers...');
            $this->assignTeachers($school, $sections, $subjectsByClass, $teachers);

            $this->info('Creating non-teaching staff...');
            $this->createSupportStaff($school, $hashedPassword);

            $this->info("Creating students ({$studentsPerSection} per section)...");
            $students = $this->createStudents($school, $sections, $hashedPassword, $studentsPerSection);

            $this->info('Creating textbooks and loans...');
            $this->createTextbooksAndLoans($school, $students);

            $this->info('Generating one term of attendance (this is the slow part)...');
            $this->createTermAttendance($school, $students, $teachers);

            $this->newLine();
            $this->info('Done. Summary:');
            $this->line('  Classes: 9 (Grade 1-9), Sections: '.count($sections));
            $this->line('  Teachers: 60');
            $this->line('  Students: '.count($students));
            $this->line('  Sample logins: teacher1 / student1 through however many were created — same password you just entered.');
        });

        return self::SUCCESS;
    }

    protected function wipeExisting(School $school): void
    {
        // Cascade order matters: children before parents. school_id foreign
        // keys already cascade on the School row itself, but we're keeping
        // the School — just clearing what's inside it — so each table is
        // cleared explicitly, deepest dependents first.
        $studentIds = Student::where('school_id', $school->id)->pluck('id');
        Attendance::whereIn('student_id', $studentIds)->delete();

        if (Schema::hasTable('textbook_loans')) {
            if (Schema::hasColumn('textbook_loans', 'school_id')) {
                DB::table('textbook_loans')->where('school_id', $school->id)->delete();
            } elseif (Schema::hasColumn('textbook_loans', 'student_id')) {
                // No direct school_id column — scoped through the student instead.
                DB::table('textbook_loans')->whereIn('student_id', $studentIds)->delete();
            }
        }
        if (Schema::hasTable('textbooks') && Schema::hasColumn('textbooks', 'school_id')) {
            DB::table('textbooks')->where('school_id', $school->id)->delete();
        }

        // class_subject_teacher has no school_id column of its own — it's
        // scoped indirectly through school_class_id (which does belong to
        // a school).
        $classIds = SchoolClass::where('school_id', $school->id)->pluck('id');
        ClassSubjectTeacher::whereIn('school_class_id', $classIds)->delete();

        $studentUserIds = Student::where('school_id', $school->id)->pluck('user_id');
        Student::where('school_id', $school->id)->delete();

        $teacherUserIds = Teacher::where('school_id', $school->id)->pluck('user_id');
        Employee::where('school_id', $school->id)->delete();
        Teacher::where('school_id', $school->id)->delete();

        User::whereIn('id', $studentUserIds->merge($teacherUserIds))->delete();

        // Catches anything the two lines above miss — e.g. a leftover demo
        // account from an earlier `php artisan db:seed` run that was never
        // properly linked to a Teacher/Student row in the first place.
        // Admins are deliberately spared so the school's real login survives.
        User::where('school_id', $school->id)->where('role', '!=', 'admin')->delete();

        if (Schema::hasColumn('subjects', 'school_id')) {
            Subject::where('school_id', $school->id)->delete();
        } else {
            Subject::whereIn('school_class_id', $classIds)->delete();
        }

        if (Schema::hasColumn('sections', 'school_id')) {
            Section::where('school_id', $school->id)->delete();
        } else {
            Section::whereIn('school_class_id', $classIds)->delete();
        }

        SchoolClass::where('school_id', $school->id)->delete();
    }

    protected function createClassesAndSections(School $school): array
    {
        $sections = [];
        foreach (range(1, 9) as $grade) {
            $class = SchoolClass::create($this->schoolIdIfSupported('school_classes', $school->id) + ['name' => "Grade {$grade}"]);
            foreach (['Green', 'Yellow', 'Blue'] as $stream) {
                $section = Section::create($this->schoolIdIfSupported('sections', $school->id) + ['school_class_id' => $class->id, 'name' => $stream]);
                $sections[] = ['class' => $class, 'section' => $section, 'grade' => $grade];
            }
        }

        return $sections;
    }

    /**
     * A representative (not exhaustive) set of CBC subjects per grade band —
     * enough to realistically exercise timetabling, results entry, and
     * teacher assignment without needing every optional JSS subject.
     */
    protected function createSubjects(School $school, array $sections): array
    {
        $lowerPrimary = ['English', 'Kiswahili', 'Mathematics', 'Environmental Activities', 'Religious Education', 'Creative Activities'];
        $upperPrimary = ['English', 'Kiswahili', 'Mathematics', 'Science and Technology', 'Social Studies', 'Religious Education', 'Creative Arts', 'Agriculture'];
        $juniorSecondary = ['English', 'Kiswahili', 'Mathematics', 'Integrated Science', 'Social Studies', 'Religious Education', 'Pre-Technical Studies', 'Business Studies', 'Agriculture', 'Life Skills'];

        $byClass = [];
        $classesSeen = [];
        foreach ($sections as $row) {
            $classId = $row['class']->id;
            if (isset($classesSeen[$classId])) {
                continue;
            }
            $classesSeen[$classId] = true;

            $grade = $row['grade'];
            $subjectNames = $grade <= 3 ? $lowerPrimary : ($grade <= 6 ? $upperPrimary : $juniorSecondary);

            $byClass[$classId] = [];
            foreach ($subjectNames as $name) {
                $code = strtoupper(Str::slug($name, '')).$grade;
                $byClass[$classId][] = Subject::create($this->schoolIdIfSupported('subjects', $school->id) + [
                    'school_class_id' => $classId,
                    'name' => $name,
                    'code' => substr($code, 0, 10),
                ]);
            }
        }

        return $byClass;
    }

    protected function nextName(): array
    {
        $isMale = $this->nameCounter % 2 === 0;
        $this->nameCounter++;
        $first = $isMale
            ? $this->firstNamesM[array_rand($this->firstNamesM)]
            : $this->firstNamesF[array_rand($this->firstNamesF)];
        $last = $this->lastNames[array_rand($this->lastNames)];

        return [$first, $last];
    }

    protected function uniqueEmailUsername(string $first, string $last, string $prefix, int $n): array
    {
        $username = strtolower($prefix.$n);
        $email = strtolower($prefix.$n.'@greenwood.demo');

        return [$username, $email];
    }

    protected function createTeachers(School $school, string $hashedPassword, int $count): array
    {
        $teachers = [];
        for ($i = 1; $i <= $count; $i++) {
            [$first, $last] = $this->nextName();
            [$username, $email] = $this->uniqueEmailUsername($first, $last, 'teacher', $i);

            $user = User::create($this->schoolIdIfSupported('users', $school->id) + [
                'name' => "{$first} {$last}",
                'email' => $email,
                'username' => $username,
                'password' => $hashedPassword,
                'role' => 'teacher',
                'is_active' => true,
            ]);

            $teacher = Teacher::create($this->schoolIdIfSupported('teachers', $school->id) + [
                'user_id' => $user->id,
                'employee_id' => 'TCH-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'qualification' => 'Diploma in Education (CBC)',
                'joining_date' => now()->subYears(rand(1, 8))->subDays(rand(0, 300)),
            ]);

            $teachers[] = $teacher;
        }

        return $teachers;
    }

    /**
     * Each of the 27 sections gets a class teacher, and every subject in
     * every class gets a subject teacher — reusing the same 60 teachers
     * across multiple classes/subjects, exactly as a real school does.
     */
    protected function assignTeachers(School $school, array $sections, array $subjectsByClass, array $teachers): void
    {
        $teacherCount = count($teachers);
        $cursor = 0;
        $nextTeacher = function () use ($teachers, $teacherCount, &$cursor) {
            $t = $teachers[$cursor % $teacherCount];
            $cursor++;

            return $t;
        };

        // Class teachers — one per section.
        foreach ($sections as $row) {
            $teacher = $nextTeacher();
            $row['section']->update(['class_teacher_id' => $teacher->id]);
        }

        // Subject teachers — every subject in every class gets a teacher,
        // cycling through the pool so a teacher naturally ends up teaching
        // several subjects across several classes.
        foreach ($sections as $row) {
            $classId = $row['class']->id;
            foreach ($subjectsByClass[$classId] ?? [] as $subject) {
                $teacher = $nextTeacher();
                ClassSubjectTeacher::firstOrCreate([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'school_class_id' => $classId,
                    'section_id' => $row['section']->id,
                ]);
            }
        }

        // Give every teacher a payroll Employee record too, so payroll
        // features have real data to work with.
        foreach ($teachers as $i => $teacher) {
            Employee::create($this->schoolIdIfSupported('employees', $school->id) + [
                'teacher_id' => $teacher->id,
                'user_id' => $teacher->user_id,
                'name' => $teacher->user->name,
                'job_title' => 'Teacher',
                'is_teaching_staff' => true,
                'basic_salary' => rand(28, 55) * 1000,
                'house_allowance' => rand(5, 12) * 1000,
                'transport_allowance' => rand(2, 6) * 1000,
                'other_allowances' => 0,
                'employment_date' => $teacher->joining_date,
            ]);
        }
    }

    protected function createSupportStaff(School $school, string $hashedPassword): void
    {
        $roles = [
            'Bursar', 'Librarian', 'School Cook', 'School Cook', 'Cleaner', 'Cleaner', 'Cleaner',
            'Driver', 'Driver', 'Security Guard', 'Security Guard', 'Groundskeeper', 'IT Support', 'Nurse',
        ];

        // Deliberately no User/login account for these — support staff
        // (cooks, drivers, cleaners) don't typically need system access,
        // just an HR/payroll record. This also avoids guessing at whether
        // "staff" is a valid value for the users.role column, which I
        // haven't seen the exact definition of.
        foreach ($roles as $jobTitle) {
            [$first, $last] = $this->nextName();

            Employee::create($this->schoolIdIfSupported('employees', $school->id) + [
                'teacher_id' => null,
                'user_id' => null,
                'name' => "{$first} {$last}",
                'job_title' => $jobTitle,
                'is_teaching_staff' => false,
                'basic_salary' => rand(15, 30) * 1000,
                'house_allowance' => rand(2, 6) * 1000,
                'transport_allowance' => rand(1, 3) * 1000,
                'other_allowances' => 0,
                'employment_date' => now()->subYears(rand(1, 6))->subDays(rand(0, 300)),
            ]);
        }
    }

    protected function createStudents(School $school, array $sections, string $hashedPassword, int $perSection): array
    {
        $students = [];
        $admissionCounter = 1;

        foreach ($sections as $row) {
            for ($i = 1; $i <= $perSection; $i++) {
                [$first, $last] = $this->nextName();
                [$username, $email] = $this->uniqueEmailUsername($first, $last, 'student', $admissionCounter);
                $guardianFirst = $this->firstNamesM[array_rand($this->firstNamesM)];

                $user = User::create($this->schoolIdIfSupported('users', $school->id) + [
                    'name' => "{$first} {$last}",
                    'email' => $email,
                    'username' => $username,
                    'password' => $hashedPassword,
                    'role' => 'student',
                    'is_active' => true,
                ]);

                $student = Student::create($this->schoolIdIfSupported('students', $school->id) + [
                    'user_id' => $user->id,
                    'admission_no' => 'GRN-'.str_pad((string) $admissionCounter, 5, '0', STR_PAD_LEFT),
                    'school_class_id' => $row['class']->id,
                    'section_id' => $row['section']->id,
                    'guardian_name' => "{$guardianFirst} {$last}",
                    'guardian_phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'dob' => now()->subYears(5 + $row['grade'])->subDays(rand(0, 300)),
                    'address' => 'Nairobi, Kenya',
                ]);

                $students[] = $student;
                $admissionCounter++;
            }
        }

        return $students;
    }

    protected function createTextbooksAndLoans(School $school, array $students): void
    {
        if (! Schema::hasTable('textbooks')) {
            $this->warn('No textbooks table found — skipping book/loan demo data.');

            return;
        }

        $titles = [
            ['English Grade 7 Learner\'s Book', 'KICD'],
            ['Kiswahili Kitabu cha Mwanafunzi', 'KICD'],
            ['Mathematics Grade 8', 'KICD'],
            ['Integrated Science Grade 9', 'KICD'],
            ['Social Studies Grade 6', 'KICD'],
            ['Agriculture Grade 7', 'KICD'],
            ['Business Studies Grade 9', 'KICD'],
            ['Creative Arts Grade 5', 'KICD'],
            ['Pre-Technical Studies Grade 8', 'KICD'],
            ['Life Skills Grade 9', 'KICD'],
        ];

        $textbookColumns = Schema::getColumnListing('textbooks');
        $bookIds = [];

        foreach ($titles as [$title, $author]) {
            $row = ['created_at' => now(), 'updated_at' => now()];
            if (in_array('school_id', $textbookColumns)) {
                $row['school_id'] = $school->id;
            }
            if (in_array('title', $textbookColumns)) {
                $row['title'] = $title;
            }
            if (in_array('author', $textbookColumns)) {
                $row['author'] = $author;
            }
            if (in_array('isbn', $textbookColumns)) {
                $row['isbn'] = '978-'.rand(1000000000, 9999999999);
            }
            if (in_array('total_copies', $textbookColumns)) {
                $row['total_copies'] = 30;
            }
            if (in_array('available_copies', $textbookColumns)) {
                $row['available_copies'] = 22;
            }
            if (in_array('quantity', $textbookColumns)) {
                $row['quantity'] = 30;
            }

            $bookIds[] = DB::table('textbooks')->insertGetId($row);
        }

        if (! Schema::hasTable('textbook_loans') || empty($bookIds)) {
            return;
        }

        $loanColumns = Schema::getColumnListing('textbook_loans');
        $borrowers = array_rand($students, min(80, count($students)));
        $loanRows = [];

        foreach ((array) $borrowers as $idx) {
            $student = $students[$idx];
            $row = [
                'student_id' => $student->id,
                'textbook_id' => $bookIds[array_rand($bookIds)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (in_array('school_id', $loanColumns)) {
                $row['school_id'] = $school->id;
            }
            $borrowedAt = now()->subDays(rand(3, 40));
            if (in_array('borrowed_at', $loanColumns)) {
                $row['borrowed_at'] = $borrowedAt;
            } elseif (in_array('loan_date', $loanColumns)) {
                $row['loan_date'] = $borrowedAt;
            }
            if (in_array('due_date', $loanColumns)) {
                $row['due_date'] = (clone $borrowedAt)->addDays(30);
            }
            if (in_array('returned_at', $loanColumns)) {
                $row['returned_at'] = rand(0, 1) ? (clone $borrowedAt)->addDays(rand(5, 20)) : null;
            }
            if (in_array('status', $loanColumns)) {
                $row['status'] = $row['returned_at'] ?? null ? 'returned' : 'borrowed';
            }

            $loanRows[] = $row;
        }

        foreach (array_chunk($loanRows, 200) as $chunk) {
            DB::table('textbook_loans')->insert($chunk);
        }
    }

    /**
     * One school term \u2248 13 weeks of weekdays. Bulk-inserted in chunks —
     * with ~540 students this is around 35,000 rows, which would be far too
     * slow through individual Eloquent::create() calls.
     */
    protected function createTermAttendance(School $school, array $students, array $teachers): void
    {
        $days = [];
        $cursor = now()->subWeeks(13);
        $end = now();
        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $days[] = $cursor->toDateString();
            }
            $cursor = $cursor->copy()->addDay();
        }

        $markerId = $teachers[0]->user_id ?? null;
        $hasSchoolId = Schema::hasColumn('attendances', 'school_id');
        $rows = [];
        $now = now();

        foreach ($students as $student) {
            foreach ($days as $date) {
                // Realistic distribution: mostly present, occasional
                // absence/lateness, rare excused leave.
                $roll = rand(1, 100);
                $status = match (true) {
                    $roll <= 88 => 'present',
                    $roll <= 95 => 'absent',
                    $roll <= 99 => 'late',
                    default => 'excused',
                };

                $row = [
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => $status,
                    'marked_by' => $markerId,
                    'remarks' => $status === 'present' ? null : ucfirst($status).' (demo data)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($hasSchoolId) {
                    $row['school_id'] = $school->id;
                }
                $rows[] = $row;

                if (count($rows) >= 2000) {
                    DB::table('attendances')->insert($rows);
                    $rows = [];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('attendances')->insert($rows);
        }
    }
}
