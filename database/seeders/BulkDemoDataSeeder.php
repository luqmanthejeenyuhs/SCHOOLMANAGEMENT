<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Bulk-inserts realistic test data for one school: 500 students, 30
 * teachers, and 23 non-teaching staff (5 of whom get a login so you can
 * test a "staff" account with limited permissions).
 *
 * Safe to re-run: every user is created with firstOrCreate keyed on email,
 * so running this twice won't create duplicates or a second batch.
 *
 * Run with:
 *   php artisan db:seed --class=Database\\Seeders\\BulkDemoDataSeeder
 */
class BulkDemoDataSeeder extends Seeder
{
    // Change this if your test data should go into a different school.
    protected int $schoolId = 1;

    protected string $password = 'password';

    protected int $studentCount = 500;

    protected int $teacherCount = 30;

    protected int $staffCount = 23;

    protected array $firstNames = [
        'Amina', 'Brian', 'Cynthia', 'Daniel', 'Esther', 'Felix', 'Grace', 'Hassan',
        'Irene', 'James', 'Kevin', 'Lucy', 'Moses', 'Nancy', 'Otieno', 'Peter',
        'Ruth', 'Samuel', 'Tabitha', 'Victor', 'Wanjiru', 'Yusuf', 'Zainab', 'Abdi',
        'Beatrice', 'Charles', 'Diana', 'Eliud', 'Faith', 'George', 'Halima', 'Ian',
        'Joyce', 'Kiptoo', 'Linet', 'Martin', 'Naomi', 'Omondi', 'Purity', 'Quinter',
        'Rose', 'Stephen', 'Teresa', 'Umar', 'Vivian', 'Wafula', 'Xavier', 'Yvonne',
        'Zablon', 'Agnes',
    ];

    protected array $lastNames = [
        'Wanjiru', 'Otieno', 'Kiptoo', 'Achieng', 'Mwangi', 'Nyambura', 'Hassan',
        'Kamau', 'Njoroge', 'Wafula', 'Odhiambo', 'Cherono', 'Mutua', 'Wekesa',
        'Chebet', 'Barasa', 'Muriithi', 'Adhiambo', 'Kilonzo', 'Simiyu', 'Waweru',
        'Njeri', 'Onyango', 'Kiprotich', 'Nyakundi', 'Mbugua', 'Auma', 'Too',
        'Wairimu', 'Kiplagat',
    ];

    protected array $staffJobTitles = [
        'Bursar', 'Accounts Clerk', 'Front Office Administrator', 'Librarian',
        'School Nurse', 'Laboratory Technician', 'ICT Support Technician',
        'Groundskeeper', 'Head Cook', 'Assistant Cook', 'Cleaner', 'Cleaner',
        'Security Guard', 'Security Guard', 'Driver', 'Store Keeper',
        'Sports Coordinator', 'Counselor', 'Human Resource Officer',
        'Procurement Officer', 'Watchman', 'Kitchen Assistant', 'Transport Coordinator',
    ];

    public function run(): void
    {
        $school = School::find($this->schoolId);

        if (! $school) {
            $this->command->error("No school found with id {$this->schoolId}. Edit \$schoolId in BulkDemoDataSeeder.");

            return;
        }

        Tenant::runFor($school->id, function () use ($school) {
            $classes = $this->ensureClasses();
            $teachers = $this->seedTeachers();
            $staffLogins = $this->seedStaff();
            $this->seedStudents($classes);

            $this->command->info('');
            $this->command->info("Done seeding for {$school->name} (school_id {$school->id}).");
            $this->command->info('All new accounts use the password: '.$this->password);
            $this->command->info('');
            $this->command->info('Sample teacher login: '.$teachers[0]->user->email);
            $this->command->info('Staff (limited-permission "admin" role) logins you can test:');
            foreach ($staffLogins as $login) {
                $this->command->info("  - {$login['email']} — permissions: {$login['permissions']}");
            }
        });
    }

    /**
     * Uses existing classes/sections if any exist; otherwise creates a
     * simple Grade 7 - Grade 12 structure with A/B streams.
     */
    protected function ensureClasses()
    {
        $classes = SchoolClass::with('sections')->get();

        if ($classes->isEmpty()) {
            foreach (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'] as $name) {
                $class = SchoolClass::create(['name' => $name]);
                Section::create(['school_class_id' => $class->id, 'name' => 'A']);
                Section::create(['school_class_id' => $class->id, 'name' => 'B']);
            }
            $classes = SchoolClass::with('sections')->get();
        }

        return $classes;
    }

    protected function randomName(int $seed): string
    {
        return $this->firstNames[$seed % count($this->firstNames)].' '.
            $this->lastNames[($seed * 7) % count($this->lastNames)];
    }

    protected function seedTeachers(): array
    {
        $teachers = [];

        for ($i = 1; $i <= $this->teacherCount; $i++) {
            $name = $this->randomName($i + 1000);
            $email = 'demo.teacher'.$i.'@school.test';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($this->password),
                    'role' => 'teacher',
                    'phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                ]
            );

            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'PENDING-'.uniqid(),
                    'qualification' => collect(['B.Ed', 'B.Sc', 'B.A', 'M.Ed'])->random(),
                    'joining_date' => now()->subDays(rand(30, 2000)),
                ]
            );

            if (str_starts_with($teacher->employee_id, 'PENDING-')) {
                $teacher->update(['employee_id' => 'EMPL'.str_pad((string) $teacher->id, 3, '0', STR_PAD_LEFT)]);
            }

            $teachers[] = $teacher;
        }

        return $teachers;
    }

    protected function seedStaff(): array
    {
        // The app has no separate "staff" login role — a staff member who
        // needs to log in is an "admin" role user restricted to specific
        // permissions (see Permission/PermissionMiddleware). We give 5 of
        // the 23 staff a login each with a different permission set so you
        // can test different access levels; the rest are payroll-only
        // records with no login, same as the existing seeder's "Bursar".
        $loginProfiles = [
            ['title' => 'Front Office Administrator', 'permissions' => ['view_students', 'create_student', 'edit_student']],
            ['title' => 'Bursar', 'permissions' => ['record_payments', 'manage_invoices', 'manage_fee_types']],
            ['title' => 'Human Resource Officer', 'permissions' => ['manage_employees', 'generate_payslips', 'view_staff_attendance']],
            ['title' => 'Librarian', 'permissions' => ['manage_textbooks']],
            ['title' => 'Store Keeper', 'permissions' => ['manage_inventory']],
        ];

        $staffLogins = [];

        for ($i = 1; $i <= $this->staffCount; $i++) {
            $jobTitle = $this->staffJobTitles[($i - 1) % count($this->staffJobTitles)];
            $name = $this->randomName($i + 5000);

            $loginProfile = $i <= count($loginProfiles) ? $loginProfiles[$i - 1] : null;
            $user = null;

            if ($loginProfile) {
                $email = 'demo.staff'.$i.'@school.test';
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make($this->password),
                        'role' => 'admin',
                        'is_super_admin' => false,
                    ]
                );

                $permissionIds = Permission::whereIn('key', $loginProfile['permissions'])->pluck('id');
                $user->permissions()->syncWithoutDetaching($permissionIds);

                $staffLogins[] = ['email' => $email, 'permissions' => implode(', ', $loginProfile['permissions'])];
                $jobTitle = $loginProfile['title'];
            }

            Employee::firstOrCreate(
                ['user_id' => $user?->id, 'name' => $name, 'job_title' => $jobTitle],
                [
                    'is_teaching_staff' => false,
                    'phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'basic_salary' => rand(20, 55) * 1000,
                    'house_allowance' => rand(3, 8) * 1000,
                    'transport_allowance' => rand(2, 5) * 1000,
                    'employment_date' => now()->subDays(rand(30, 1500)),
                ]
            );
        }

        return $staffLogins;
    }

    protected function seedStudents($classes): void
    {
        $classList = $classes->values();

        for ($i = 1; $i <= $this->studentCount; $i++) {
            $name = $this->randomName($i);
            $email = 'demo.student'.$i.'@school.test';

            $existing = User::where('email', $email)->first();
            if ($existing) {
                continue;
            }

            $class = $classList[($i - 1) % $classList->count()];
            $section = $class->sections->isNotEmpty()
                ? $class->sections[($i - 1) % $class->sections->count()]
                : null;

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($this->password),
                'role' => 'student',
            ]);

            Student::create([
                'user_id' => $user->id,
                'admission_no' => 'DEMO-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'school_class_id' => $class->id,
                'section_id' => $section?->id,
                'school_level' => str_contains($class->name, '11') || str_contains($class->name, '12') || str_contains($class->name, '10')
                    ? 'senior' : 'junior',
                'upi_number' => 'UPI-DEMO-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'guardian_name' => 'Guardian of '.$name,
                'guardian_phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'dob' => now()->subYears(rand(10, 18))->subDays(rand(0, 300)),
                'address' => 'Nairobi, Kenya',
            ]);
        }
    }
}
