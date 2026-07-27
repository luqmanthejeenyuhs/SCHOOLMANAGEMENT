<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\CbcCompetencyRecord;
use App\Models\CbcLearningArea;
use App\Models\CbcStrand;
use App\Models\CbcSubStrand;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin ---
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@school.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // --- Classes & sections & subjects ---
        $grade9 = SchoolClass::create(['name' => 'Grade 9']);
        $grade10 = SchoolClass::create(['name' => 'Grade 10']);

        $g9A = Section::create(['school_class_id' => $grade9->id, 'name' => 'A']);
        $g10A = Section::create(['school_class_id' => $grade10->id, 'name' => 'A']);
        $g10B = Section::create(['school_class_id' => $grade10->id, 'name' => 'B']);

        $mathG9 = Subject::create(['school_class_id' => $grade9->id, 'name' => 'Mathematics', 'code' => 'MTH9']);
        $engG9 = Subject::create(['school_class_id' => $grade9->id, 'name' => 'English', 'code' => 'ENG9']);
        $mathG10 = Subject::create(['school_class_id' => $grade10->id, 'name' => 'Mathematics', 'code' => 'MTH10']);
        $sciG10 = Subject::create(['school_class_id' => $grade10->id, 'name' => 'Science', 'code' => 'SCI10']);

        // --- Teachers ---
        $teacherUser1 = User::create([
            'name' => 'Grace Wanjiru',
            'email' => 'teacher1@school.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);
        $teacher1 = Teacher::create([
            'user_id' => $teacherUser1->id,
            'employee_id' => 'EMP-001',
            'qualification' => 'B.Ed Mathematics',
            'joining_date' => now()->subYears(3),
        ]);

        $teacherUser2 = User::create([
            'name' => 'David Otieno',
            'email' => 'teacher2@school.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);
        $teacher2 = Teacher::create([
            'user_id' => $teacherUser2->id,
            'employee_id' => 'EMP-002',
            'qualification' => 'B.Sc Chemistry',
            'joining_date' => now()->subYears(1),
        ]);

        $teacher1->assignments()->create([
            'subject_id' => $mathG10->id, 'school_class_id' => $grade10->id, 'section_id' => $g10A->id,
        ]);
        $teacher2->assignments()->create([
            'subject_id' => $sciG10->id, 'school_class_id' => $grade10->id, 'section_id' => $g10A->id,
        ]);

        // --- Class teachers for each stream ---
        $g9A->update(['class_teacher_id' => $teacher1->id]);
        $g10A->update(['class_teacher_id' => $teacher1->id]);
        $g10B->update(['class_teacher_id' => $teacher2->id]);

        // --- Fee types ---
        $tuition = FeeType::create(['name' => 'Tuition Fee', 'amount' => 15000, 'frequency' => 'term']);
        $transport = FeeType::create(['name' => 'Transport Fee', 'amount' => 3000, 'frequency' => 'term']);

        // --- Students ---
        $studentNames = [
            ['Amina Hassan', $grade10, $g10A],
            ['Brian Kiptoo', $grade10, $g10A],
            ['Cynthia Achieng', $grade10, $g10A],
            ['Daniel Mwangi', $grade9, $g9A],
            ['Esther Nyambura', $grade9, $g9A],
        ];

        $students = [];
        foreach ($studentNames as $i => [$name, $class, $section]) {
            $u = User::create([
                'name' => $name,
                'email' => 'student'.($i + 1).'@school.test',
                'password' => Hash::make('password'),
                'role' => 'student',
            ]);

            $student = Student::create([
                'user_id' => $u->id,
                'admission_no' => 'ADM-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'school_level' => $class->name === 'Grade 10' ? 'senior' : 'junior',
                'pathway' => $class->name === 'Grade 10' ? 'STEM' : null,
                'upi_number' => 'UPI-KE-'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'guardian_name' => 'Guardian of '.$name,
                'guardian_phone' => '2547'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'dob' => now()->subYears(15)->subDays($i * 30),
                'address' => 'Nairobi, Kenya',
            ]);

            $students[] = $student;

            // Attendance for the last 5 days
            for ($d = 4; $d >= 0; $d--) {
                Attendance::create([
                    'student_id' => $student->id,
                    'date' => now()->subDays($d)->toDateString(),
                    'status' => $i === 1 && $d === 2 ? 'absent' : 'present',
                    'marked_by' => $teacher1->user_id,
                ]);
            }

            // Fee invoice + partial payment for demo variety
            $invoice = FeeInvoice::create([
                'student_id' => $student->id,
                'fee_type_id' => $tuition->id,
                'amount' => $tuition->amount,
                'due_date' => now()->addDays(14),
                'status' => 'unpaid',
            ]);

            if ($i % 2 === 0) {
                Payment::create([
                    'fee_invoice_id' => $invoice->id,
                    'amount_paid' => $tuition->amount,
                    'payment_date' => now()->subDays(2),
                    'method' => 'mpesa',
                    'received_by' => $admin->id,
                ]);
                $invoice->update(['status' => 'paid']);
            } else {
                Payment::create([
                    'fee_invoice_id' => $invoice->id,
                    'amount_paid' => 5000,
                    'payment_date' => now()->subDays(1),
                    'method' => 'cash',
                    'received_by' => $admin->id,
                ]);
                $invoice->update(['status' => 'partially_paid']);
            }

            FeeInvoice::create([
                'student_id' => $student->id,
                'fee_type_id' => $transport->id,
                'amount' => $transport->amount,
                'due_date' => now()->addDays(14),
                'status' => 'unpaid',
            ]);
        }

        // --- Extra-curricular activities ---
        $swimming = Activity::create([
            'name' => 'Swimming',
            'patron_id' => $teacher2->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'venue' => 'School Pool',
            'description' => 'Weekly swimming training for all levels, beginners welcome.',
        ]);

        $debate = Activity::create([
            'name' => 'Debate Club',
            'patron_id' => $teacher1->id,
            'day_of_week' => 'Friday',
            'start_time' => '15:00',
            'end_time' => '16:00',
            'venue' => 'Library',
            'description' => 'Builds public speaking and critical thinking skills.',
        ]);

        foreach (array_slice($students, 0, 3) as $student) {
            $swimming->students()->attach($student->id, ['signed_up_at' => now()->subDays(5)]);
        }
        foreach (array_slice($students, 2, 3) as $student) {
            $debate->students()->attach($student->id, ['signed_up_at' => now()->subDays(2)]);
        }

        // --- Exam & results for Grade 10 A ---
        $exam = Exam::create([
            'name' => 'Mid-Term Exam',
            'school_class_id' => $grade10->id,
            'term' => 'Term 2',
            'exam_date' => now()->subDays(3),
        ]);

        foreach (array_slice($students, 0, 3) as $student) {
            foreach ([$mathG10, $sciG10] as $subject) {
                $marks = rand(45, 95);
                $percentage = $marks; // max 100
                $grade = match (true) {
                    $percentage >= 80 => 'A',
                    $percentage >= 70 => 'B',
                    $percentage >= 60 => 'C',
                    $percentage >= 50 => 'D',
                    default => 'F',
                };

                ExamResult::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'marks_obtained' => $marks,
                    'max_marks' => 100,
                    'grade' => $grade,
                ]);
            }
        }

        // --- CBC Curriculum (Junior School demo: Mathematics learning area) ---
        $mathematicsCbc = CbcLearningArea::create(['name' => 'Mathematics', 'school_level' => 'junior']);
        $numbersStrand = CbcStrand::create(['cbc_learning_area_id' => $mathematicsCbc->id, 'name' => 'Numbers']);
        $wholeNumbers = CbcSubStrand::create(['cbc_strand_id' => $numbersStrand->id, 'name' => 'Whole Numbers']);
        $fractions = CbcSubStrand::create(['cbc_strand_id' => $numbersStrand->id, 'name' => 'Fractions']);

        $englishCbc = CbcLearningArea::create(['name' => 'English', 'school_level' => 'junior']);
        $listeningStrand = CbcStrand::create(['cbc_learning_area_id' => $englishCbc->id, 'name' => 'Listening & Speaking']);
        $listeningSkills = CbcSubStrand::create(['cbc_strand_id' => $listeningStrand->id, 'name' => 'Listening Comprehension']);

        // Sample ratings for the Grade 9 (junior) students
        foreach (array_slice($students, 3, 2) as $student) {
            foreach ([$wholeNumbers, $fractions, $listeningSkills] as $subStrand) {
                CbcCompetencyRecord::create([
                    'student_id' => $student->id,
                    'cbc_sub_strand_id' => $subStrand->id,
                    'term' => 'Term 1 '.now()->year,
                    'performance_level' => collect(['EE', 'ME', 'AE'])->random(),
                    'recorded_by' => $teacherUser1->id,
                ]);
            }
        }

        // --- Staff & Payroll demo data ---
        Employee::create([
            'teacher_id' => $teacher1->id,
            'name' => $teacherUser1->name,
            'job_title' => 'Mathematics Teacher',
            'is_teaching_staff' => true,
            'kra_pin' => 'A012345678B',
            'nssf_number' => 'NSSF-100234',
            'shif_number' => 'SHIF-550012',
            'phone' => '254711000111',
            'basic_salary' => 45000,
            'house_allowance' => 8000,
            'transport_allowance' => 5000,
            'employment_date' => now()->subYears(3),
        ]);

        Employee::create([
            'teacher_id' => $teacher2->id,
            'name' => $teacherUser2->name,
            'job_title' => 'Science Teacher',
            'is_teaching_staff' => true,
            'kra_pin' => 'A012345679C',
            'nssf_number' => 'NSSF-100235',
            'shif_number' => 'SHIF-550013',
            'phone' => '254711000112',
            'basic_salary' => 40000,
            'house_allowance' => 7000,
            'transport_allowance' => 5000,
            'employment_date' => now()->subYear(),
        ]);

        Employee::create([
            'name' => 'Peter Mutua',
            'job_title' => 'School Bursar',
            'is_teaching_staff' => false,
            'kra_pin' => 'A012345680D',
            'nssf_number' => 'NSSF-100236',
            'shif_number' => 'SHIF-550014',
            'phone' => '254711000113',
            'basic_salary' => 35000,
            'house_allowance' => 5000,
            'transport_allowance' => 3000,
            'employment_date' => now()->subYears(2),
        ]);

        $this->command->info('Demo data seeded. Login as admin@school.test / teacher1@school.test / student1@school.test — password for all: "password"');
    }
}
