<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\CbcController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamResultController as AdminExamResultController;
use App\Http\Controllers\Admin\FeeInvoiceController;
use App\Http\Controllers\Admin\FeeTypeController;
use App\Http\Controllers\Admin\FinanceLedgerController;
use App\Http\Controllers\Admin\GradingScaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MpesaController;
use App\Http\Controllers\Admin\PayslipController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TextbookController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\CbcAssessmentController;
use App\Http\Controllers\Teacher\ClockController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ExamResultController as TeacherExamResultController;
use App\Http\Controllers\Webhooks\BankWebhookController;
use App\Http\Controllers\Webhooks\MpesaC2bWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboardController;

// --- Server-to-server webhooks: no auth, no CSRF (see VerifyCsrfToken::$except) ---
Route::post('/mpesa/c2b/validation', [MpesaC2bWebhookController::class, 'validation'])->name('mpesa.c2b.validation');
Route::post('/mpesa/c2b/confirmation', [MpesaC2bWebhookController::class, 'confirmation'])->name('mpesa.c2b.confirmation');
Route::post('/webhooks/bank/deposit', [BankWebhookController::class, 'handle'])->name('webhooks.bank.deposit');

// M-Pesa Daraja callback — Safaricom calls this server-to-server, so it must sit
// completely outside the "auth"/"web" CSRF-protected group.
Route::post('/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');

Route::get('/', fn () => redirect('/login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ADMIN
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('teachers', TeacherController::class);
        Route::get('teachers/{teacher}/documents/{document}/download', [TeacherController::class, 'downloadDocument'])->name('teachers.documents.download');
        Route::delete('teachers/{teacher}/documents/{document}', [TeacherController::class, 'destroyDocument'])->name('teachers.documents.destroy');
        Route::resource('students', StudentController::class);
        Route::resource('parents', ParentController::class)->except(['edit', 'update']);

        Route::get('classes', [SchoolClassController::class, 'index'])->name('classes.index');
        Route::post('classes', [SchoolClassController::class, 'store'])->name('classes.store');
        Route::get('classes/{class}', [SchoolClassController::class, 'show'])->name('classes.show');
        Route::put('classes/{class}', [SchoolClassController::class, 'update'])->name('classes.update');
        Route::delete('classes/{class}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

        Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
        Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
        Route::get('sections/{section}', [SectionController::class, 'show'])->name('sections.show');
        Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        // Extra-curricular activities (swimming, clubs, sports, etc.)
        Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
        Route::put('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
        Route::post('activities/{activity}/students', [ActivityController::class, 'enroll'])->name('activities.students.store');
        Route::delete('activities/{activity}/students/{student}', [ActivityController::class, 'unenroll'])->name('activities.students.destroy');

        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
        Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store');
        Route::delete('timetable/{timetableSlot}', [TimetableController::class, 'destroy'])->name('timetable.destroy');

        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::get('exams/{exam}/results', [AdminExamResultController::class, 'show'])->name('exams.results');
        Route::get('exams/{exam}/report-card/{student}', [AdminExamResultController::class, 'reportCard'])->name('exams.report_card');
        Route::post('exams/{exam}/report-card/{student}/comment', [AdminExamResultController::class, 'storeComment'])->name('exams.comment.store');

        Route::get('grading-scales', [GradingScaleController::class, 'index'])->name('grading_scales.index');
        Route::post('grading-scales', [GradingScaleController::class, 'store'])->name('grading_scales.store');
        Route::delete('grading-scales/{gradingScale}', [GradingScaleController::class, 'destroy'])->name('grading_scales.destroy');

        Route::get('fee-types', [FeeTypeController::class, 'index'])->name('fee_types.index');
        Route::post('fee-types', [FeeTypeController::class, 'store'])->name('fee_types.store');
        Route::delete('fee-types/{feeType}', [FeeTypeController::class, 'destroy'])->name('fee_types.destroy');

        Route::get('invoices', [FeeInvoiceController::class, 'index'])->name('invoices.index');
        Route::post('invoices', [FeeInvoiceController::class, 'store'])->name('invoices.store');
        Route::post('invoices/bulk', [FeeInvoiceController::class, 'bulkStore'])->name('invoices.bulk_store');
        Route::post('invoices/{invoice}/payments', [FeeInvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::post('invoices/{invoice}/mpesa-push', [MpesaController::class, 'push'])->name('invoices.mpesa_push');
        Route::get('mpesa/transactions/{transaction}/status', [MpesaController::class, 'status'])->name('mpesa.status');

        // CBC (Competency Based Curriculum)
        Route::get('cbc', [CbcController::class, 'index'])->name('cbc.index');
        Route::post('cbc/learning-areas', [CbcController::class, 'storeLearningArea'])->name('cbc.learning_areas.store');
        Route::delete('cbc/learning-areas/{learningArea}', [CbcController::class, 'destroyLearningArea'])->name('cbc.learning_areas.destroy');
        Route::post('cbc/strands', [CbcController::class, 'storeStrand'])->name('cbc.strands.store');
        Route::delete('cbc/strands/{strand}', [CbcController::class, 'destroyStrand'])->name('cbc.strands.destroy');
        Route::post('cbc/sub-strands', [CbcController::class, 'storeSubStrand'])->name('cbc.sub_strands.store');
        Route::delete('cbc/sub-strands/{subStrand}', [CbcController::class, 'destroySubStrand'])->name('cbc.sub_strands.destroy');
        Route::get('cbc/students/{student}/report', [CbcController::class, 'report'])->name('cbc.report');
        Route::put('cbc/students/{student}/profile', [CbcController::class, 'updateLearnerProfile'])->name('cbc.profile.update');

        Route::get('cbc/core-competencies', [CbcController::class, 'coreCompetencyGrid'])->name('cbc.core_competencies.grid');
        Route::post('cbc/core-competencies', [CbcController::class, 'storeCoreCompetencies'])->name('cbc.core_competencies.store');

        Route::get('cbc/sba', [CbcController::class, 'sbaGrid'])->name('cbc.sba.grid');
        Route::post('cbc/sba', [CbcController::class, 'storeSba'])->name('cbc.sba.store');

        Route::post('cbc/portfolio', [CbcController::class, 'storePortfolio'])->name('cbc.portfolio.store');
        Route::get('cbc/portfolio/{item}/download', [CbcController::class, 'downloadPortfolio'])->name('cbc.portfolio.download');
        Route::delete('cbc/portfolio/{item}', [CbcController::class, 'destroyPortfolio'])->name('cbc.portfolio.destroy');

        Route::get('cbc/values', [CbcController::class, 'valuesGrid'])->name('cbc.values.grid');
        Route::post('cbc/values', [CbcController::class, 'storeValues'])->name('cbc.values.store');

        // Bulk SMS
        Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
        Route::post('sms', [SmsController::class, 'send'])->name('sms.send');

        // Payroll: staff + payslips
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::post('employees/{employee}/payslips', [PayslipController::class, 'generate'])->name('payslips.generate');
        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');

        // Staff Attendance
        Route::get('staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff_attendance.index');

        // Finance: Bank + M-Pesa C2B ledger (paperless deposit reconciliation)
        Route::get('finance/ledger', [FinanceLedgerController::class, 'index'])->name('finance.ledger.index');
        Route::post('finance/ledger/bank/{bankTransaction}/reconcile', [FinanceLedgerController::class, 'reconcileBank'])->name('finance.ledger.bank.reconcile');
        Route::post('finance/ledger/mpesa/{mpesaC2bTransaction}/reconcile', [FinanceLedgerController::class, 'reconcileMpesa'])->name('finance.ledger.mpesa.reconcile');

        // Inventory: stationery/consumables POS-style issuing
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::delete('inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::post('inventory/{item}/issue', [InventoryController::class, 'issue'])->name('inventory.issue');

        // Textbooks: barcode-tracked lending with auto penalty invoicing
        Route::get('textbooks', [TextbookController::class, 'index'])->name('textbooks.index');
        Route::post('textbooks/copies', [TextbookController::class, 'storeCopy'])->name('textbooks.copies.store');
        Route::post('textbooks/issue', [TextbookController::class, 'issue'])->name('textbooks.issue');
        Route::post('textbooks/return', [TextbookController::class, 'returnCopy'])->name('textbooks.return');
    });

    // TEACHER
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/results', [TeacherExamResultController::class, 'index'])->name('results.index');
        Route::post('/results', [TeacherExamResultController::class, 'store'])->name('results.store');
        Route::get('/cbc', [CbcAssessmentController::class, 'index'])->name('cbc.index');
        Route::post('/cbc', [CbcAssessmentController::class, 'store'])->name('cbc.store');
        Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
        Route::post('/clock', [ClockController::class, 'store'])->name('clock.store');
        Route::get('/timetable', [TeacherTimetableController::class, 'index'])->name('timetable.index');
    });

    // STUDENT
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/cbc-report', function (\Illuminate\Http\Request $request) {
            $student = auth()->user()->student;
            abort_if(! $student, 404);

            return app(CbcController::class)->report($student, $request);
        })->name('cbc_report');
    });

    // PARENT
    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/children/{student}', [ParentDashboardController::class, 'show'])->name('children.show');
    });
});
