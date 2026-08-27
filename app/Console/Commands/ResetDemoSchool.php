<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetDemoSchool extends Command
{
    protected $signature = 'demo:reset {--password= : Set the demo login password non-interactively (e.g. for a scheduled nightly reset)}';

    protected $description = 'Wipe and recreate the "Demo School" tenant with realistic sample data — safe to run repeatedly on production for sales demos.';

    public function handle(): int
    {
        $password = $this->option('password') ?: $this->secret('Demo account password (used for admin/teacher/student logins, input hidden)');

        if (! $password || strlen($password) < 10) {
            $this->error('Password must be at least 10 characters. Pass --password=... or run interactively.');

            return self::FAILURE;
        }

        // Wipe any previous run — cascades to every school-scoped table
        // automatically (students, invoices, payments, receipts, journal
        // entries, etc. all cascadeOnDelete on school_id).
        School::where('slug', 'demo')->each(fn (School $old) => $old->delete());

        $school = School::create([
            'name' => 'Demo School',
            'slug' => 'demo',
            'email' => 'info@demo.taalumasms.co.ke',
            'phone' => '254700000000',
            'address' => 'Nairobi, Kenya',
        ]);

        Tenant::runFor($school->id, function () use ($password) {
            $admin = User::create([
                'name' => 'Demo Admin', 'email' => 'admin@demo.taalumasms.co.ke',
                'password' => Hash::make($password), 'role' => 'admin', 'is_super_admin' => true,
            ]);

            $grade9 = SchoolClass::create(['name' => 'Grade 9']);
            $grade10 = SchoolClass::create(['name' => 'Grade 10']);
            $g9A = Section::create(['school_class_id' => $grade9->id, 'name' => 'A']);
            $g10A = Section::create(['school_class_id' => $grade10->id, 'name' => 'A']);

            $mathG10 = Subject::create(['school_class_id' => $grade10->id, 'name' => 'Mathematics', 'code' => 'MTH10']);
            $sciG10 = Subject::create(['school_class_id' => $grade10->id, 'name' => 'Science', 'code' => 'SCI10']);

            $teacherUser = User::create([
                'name' => 'Grace Wanjiru', 'email' => 'teacher@demo.taalumasms.co.ke',
                'password' => Hash::make($password), 'role' => 'teacher',
            ]);
            $teacher = Teacher::create([
                'user_id' => $teacherUser->id, 'employee_id' => 'EMP-001',
                'qualification' => 'B.Ed Mathematics', 'joining_date' => now()->subYears(3),
            ]);
            $teacher->assignments()->create([
                'subject_id' => $mathG10->id, 'school_class_id' => $grade10->id, 'section_id' => $g10A->id,
            ]);
            $g10A->update(['class_teacher_id' => $teacher->id]);

            $tuition = FeeType::create(['name' => 'Tuition Fee', 'amount' => 15000, 'frequency' => 'term']);
            $transport = FeeType::create(['name' => 'Transport Fee', 'amount' => 3000, 'frequency' => 'term']);

            $studentNames = [
                ['Amina Hassan', $grade10, $g10A],
                ['Brian Kiptoo', $grade10, $g10A],
                ['Daniel Mwangi', $grade9, $g9A],
            ];

            $students = [];
            foreach ($studentNames as $i => [$name, $class, $section]) {
                $u = User::create([
                    'name' => $name, 'email' => 'student'.($i + 1).'@demo.taalumasms.co.ke',
                    'password' => Hash::make($password), 'role' => 'student',
                ]);
                $student = Student::create([
                    'user_id' => $u->id, 'admission_no' => 'ADM-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'school_class_id' => $class->id, 'section_id' => $section->id,
                    'guardian_name' => 'Guardian of '.$name,
                    'guardian_phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'dob' => now()->subYears(15)->subDays($i * 30), 'address' => 'Nairobi, Kenya',
                ]);
                $students[] = $student;

                for ($d = 4; $d >= 0; $d--) {
                    Attendance::create([
                        'student_id' => $student->id, 'date' => now()->subDays($d)->toDateString(),
                        'status' => $i === 1 && $d === 2 ? 'absent' : 'present', 'marked_by' => $teacher->user_id,
                    ]);
                }

                $invoice = FeeInvoice::create([
                    'student_id' => $student->id, 'fee_type_id' => $tuition->id,
                    'amount' => $tuition->amount, 'due_date' => now()->addDays(14), 'status' => 'unpaid',
                ]);

                if ($i % 2 === 0) {
                    Payment::create([
                        'fee_invoice_id' => $invoice->id, 'amount_paid' => $tuition->amount,
                        'payment_date' => now()->subDays(2), 'method' => 'mpesa', 'received_by' => $admin->id,
                    ]);
                    $invoice->update(['status' => 'paid']);
                } else {
                    Payment::create([
                        'fee_invoice_id' => $invoice->id, 'amount_paid' => 5000,
                        'payment_date' => now()->subDays(1), 'method' => 'cash', 'received_by' => $admin->id,
                    ]);
                    $invoice->update(['status' => 'partially_paid']);
                }

                FeeInvoice::create([
                    'student_id' => $student->id, 'fee_type_id' => $transport->id,
                    'amount' => $transport->amount, 'due_date' => now()->addDays(14), 'status' => 'unpaid',
                ]);
            }

            $exam = Exam::create([
                'name' => 'Mid-Term Exam', 'school_class_id' => $grade10->id,
                'term' => 'Term 2', 'exam_date' => now()->subDays(3),
            ]);
            foreach (array_slice($students, 0, 2) as $student) {
                foreach ([$mathG10, $sciG10] as $subject) {
                    $marks = rand(55, 95);
                    ExamResult::create([
                        'exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $subject->id,
                        'marks_obtained' => $marks, 'max_marks' => 100,
                        'grade' => $marks >= 80 ? 'A' : ($marks >= 70 ? 'B' : ($marks >= 60 ? 'C' : 'D')),
                    ]);
                }
            }

            Activity::create([
                'name' => 'Debate Club', 'patron_id' => $teacher->id, 'day_of_week' => 'Friday',
                'start_time' => '15:00', 'end_time' => '16:00', 'venue' => 'Library',
                'description' => 'Builds public speaking and critical thinking skills.',
            ]);

            Employee::create([
                'teacher_id' => $teacher->id, 'name' => $teacherUser->name, 'job_title' => 'Mathematics Teacher',
                'is_teaching_staff' => true, 'user_id' => $teacherUser->id, 'basic_salary' => 45000,
                'house_allowance' => 8000, 'transport_allowance' => 5000, 'employment_date' => now()->subYears(3),
            ]);
        });

        $this->newLine();
        $this->info('Demo School reset. Sign in at:');
        $this->line('  https://demo.'.config('school.platform_domain', 'yourdomain.com').'/login  (once PLATFORM_DOMAIN + subdomain login are live)');
        $this->line('  or the plain /login page with school context set manually, meanwhile');
        $this->newLine();
        $this->line('  Admin:   admin@demo.taalumasms.co.ke');
        $this->line('  Teacher: teacher@demo.taalumasms.co.ke');
        $this->line('  Student: student1@demo.taalumasms.co.ke');
        $this->line('  (password: whatever you just entered — not printed here for safety)');

        return self::SUCCESS;
    }
}
