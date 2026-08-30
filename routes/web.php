<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\CbcController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamResultController as AdminExamResultController;
use App\Http\Controllers\Admin\FeeInvoiceController;
use App\Http\Controllers\Admin\FeeTypeController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\FinanceLedgerController;
use App\Http\Controllers\Admin\GradingScaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\MpesaController;
use App\Http\Controllers\Admin\PayslipController;
use App\Http\Controllers\Admin\ReceiptController;
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
use App\Http\Controllers\Teacher\LeaveController as StaffLeaveController;
use App\Http\Controllers\Teacher\PayslipController as StaffPayslipController;
use App\Http\Controllers\Webhooks\BankWebhookController;
use App\Http\Controllers\Webhooks\MpesaC2bWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\SettingsController;
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

    // Branded per-school login — {school} binds by slug (not id), since
    // that's what the URL actually carries, e.g. /school/greenwood/login.
    Route::get('/school/{school:slug}/login', [LoginController::class, 'showLoginForm'])->name('login.school');
    Route::post('/school/{school:slug}/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    // Available to every role — the same page serves the voluntary
    // "Password" nav button and the forced first-login change (see
    // App\Http\Middleware\EnsurePasswordIsChanged, aliased as
    // 'password.changed' in Kernel.php).
    Route::get('/account/password', [AccountController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Self clock-in/leave/payslips, open to anyone with a linked Employee
    // record — teachers already have /teacher/clock, /teacher/results etc;
    // this is the SAME controllers so non-teaching staff who log in with an
    // admin-role account (bursar, front office, etc.) can use them too.
    // NOTE: no staff.loans here on purpose — Staff Loans are now recorded
    // directly by an admin (see Admin\StaffLoanController), not requested
    // by staff, so there's nothing for a self-service page to submit to.
    Route::prefix('staff')->name('staff.')->middleware('role:admin,teacher')->group(function () {
        Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
        Route::post('/clock', [ClockController::class, 'store'])->name('clock.store');

        Route::get('/leave', [StaffLeaveController::class, 'index'])->name('leave.index');
        Route::post('/leave', [StaffLeaveController::class, 'store'])->name('leave.store');

        Route::get('/payslips', [StaffPayslipController::class, 'index'])->name('payslips.index');
    });

    // ADMIN
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Teachers
        Route::middleware('permission:create_teacher')->group(function () {
            Route::get('teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
            Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
        });
        Route::middleware('permission:edit_teacher')->group(function () {
            Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
            Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
            Route::post('teachers/{teacher}/assignments', [TeacherController::class, 'storeAssignment'])->name('teachers.assignments.store');
            Route::delete('teachers/{teacher}/assignments/{assignment}', [TeacherController::class, 'destroyAssignment'])->name('teachers.assignments.destroy');
        });
        Route::middleware('permission:view_teachers')->group(function () {
            Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
            Route::get('teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
        });
        Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy')->middleware('permission:delete_teacher');
        Route::middleware('permission:manage_teacher_documents')->group(function () {
            Route::get('teachers/{teacher}/documents/{document}/download', [TeacherController::class, 'downloadDocument'])->name('teachers.documents.download');
            Route::delete('teachers/{teacher}/documents/{document}', [TeacherController::class, 'destroyDocument'])->name('teachers.documents.destroy');
        });

        // Students
        Route::middleware('permission:create_student')->group(function () {
            Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
            Route::post('students', [StudentController::class, 'store'])->name('students.store');
        });
        Route::middleware('permission:edit_student')->group(function () {
            Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
        });
        Route::middleware('permission:view_students')->group(function () {
            Route::get('students', [StudentController::class, 'index'])->name('students.index');
            Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
        });
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy')->middleware('permission:delete_student');

        // Parents
        Route::middleware('permission:create_parent')->group(function () {
            Route::get('parents/create', [ParentController::class, 'create'])->name('parents.create');
            Route::post('parents', [ParentController::class, 'store'])->name('parents.store');
        });
        Route::middleware('permission:view_parents')->group(function () {
            Route::get('parents', [ParentController::class, 'index'])->name('parents.index');
            Route::get('parents/{parent}', [ParentController::class, 'show'])->name('parents.show');
        });
        Route::delete('parents/{parent}', [ParentController::class, 'destroy'])->name('parents.destroy')->middleware('permission:delete_parent');

        Route::middleware('permission:manage_classes')->group(function () {
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
        });

        // Extra-curricular activities (swimming, clubs, sports, etc.)
        Route::middleware('permission:manage_activities')->group(function () {
            Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
            Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
            Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
            Route::put('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
            Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
            Route::post('activities/{activity}/students', [ActivityController::class, 'enroll'])->name('activities.students.store');
            Route::delete('activities/{activity}/students/{student}', [ActivityController::class, 'unenroll'])->name('activities.students.destroy');
        });

        Route::middleware('permission:manage_subjects')->group(function () {
            Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
            Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        });

        Route::middleware('permission:manage_timetable')->group(function () {
            Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
            Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store');
            Route::delete('timetable/{timetableSlot}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
        });

        Route::middleware('permission:manage_exams')->group(function () {
            Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
            Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
            Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        });
        Route::middleware('permission:enter_results')->group(function () {
            Route::get('exams/{exam}/results', [AdminExamResultController::class, 'show'])->name('exams.results');
            Route::get('exams/{exam}/report-card/{student}', [AdminExamResultController::class, 'reportCard'])->name('exams.report_card');
            Route::post('exams/{exam}/report-card/{student}/comment', [AdminExamResultController::class, 'storeComment'])->name('exams.comment.store');
        });

        Route::middleware('permission:manage_grading_scales')->group(function () {
            Route::get('grading-scales', [GradingScaleController::class, 'index'])->name('grading_scales.index');
            Route::post('grading-scales', [GradingScaleController::class, 'store'])->name('grading_scales.store');
            Route::delete('grading-scales/{gradingScale}', [GradingScaleController::class, 'destroy'])->name('grading_scales.destroy');
        });

        Route::middleware('permission:manage_fee_types')->group(function () {
            Route::get('fee-types', [FeeTypeController::class, 'index'])->name('fee_types.index');
            Route::post('fee-types', [FeeTypeController::class, 'store'])->name('fee_types.store');
            Route::delete('fee-types/{feeType}', [FeeTypeController::class, 'destroy'])->name('fee_types.destroy');
        });

        Route::middleware('permission:manage_invoices')->group(function () {
            Route::get('invoices', [FeeInvoiceController::class, 'index'])->name('invoices.index');
            Route::post('invoices', [FeeInvoiceController::class, 'store'])->name('invoices.store');
            Route::post('invoices/bulk', [FeeInvoiceController::class, 'bulkStore'])->name('invoices.bulk_store');
        });
        Route::middleware('permission:record_payments')->group(function () {
            Route::post('invoices/{invoice}/payments', [FeeInvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
            Route::post('invoices/{invoice}/mpesa-push', [MpesaController::class, 'push'])->name('invoices.mpesa_push');
            Route::get('mpesa/transactions/{transaction}/status', [MpesaController::class, 'status'])->name('mpesa.status');

            Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
            Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
        });

        // CBC (Competency Based Curriculum)
        Route::middleware('permission:manage_cbc')->group(function () {
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
        });

        // Bulk SMS
        Route::middleware('permission:send_sms')->group(function () {
            Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
            Route::post('sms', [SmsController::class, 'send'])->name('sms.send');
        });

        // Payroll: staff + payslips
        Route::middleware('permission:manage_employees')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
            Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
            Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        });
        Route::middleware('permission:generate_payslips')->group(function () {
            Route::post('employees/{employee}/payslips', [PayslipController::class, 'generate'])->name('payslips.generate');
            Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
            Route::get('payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');
        });

        // Staff Attendance
        Route::get('staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff_attendance.index')->middleware('permission:view_staff_attendance');

        Route::middleware('permission:manage_leave_requests')->group(function () {
            Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
            Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave_requests.approve');
            Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave_requests.reject');
        });

        // Finance: Bank + M-Pesa C2B ledger (paperless deposit reconciliation)
        Route::middleware('permission:manage_finance_ledger')->group(function () {
            Route::get('finance/ledger', [FinanceLedgerController::class, 'index'])->name('finance.ledger.index');
            Route::post('finance/ledger/bank/{bankTransaction}/reconcile', [FinanceLedgerController::class, 'reconcileBank'])->name('finance.ledger.bank.reconcile');
            Route::post('finance/ledger/mpesa/{mpesaC2bTransaction}/reconcile', [FinanceLedgerController::class, 'reconcileMpesa'])->name('finance.ledger.mpesa.reconcile');
        });

        // Finance: full accounting — Chart of Accounts, Journal Entries, General
        // Ledger, Trial Balance. Fee payments auto-post here via
        // App\Observers\PaymentObserver, no extra wiring needed at the call site.
        Route::middleware('permission:manage_accounting')->prefix('accounting')->name('accounting.')->group(function () {
            Route::get('overview', [AccountingController::class, 'overview'])->name('overview');

            Route::get('chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('chart_of_accounts');
            Route::post('chart-of-accounts', [AccountingController::class, 'storeAccount'])->name('accounts.store');

            Route::get('journal-entries', [AccountingController::class, 'journalEntries'])->name('journal_entries');
            Route::post('journal-entries', [AccountingController::class, 'storeJournalEntry'])->name('journal_entries.store');

            Route::get('ledger', [AccountingController::class, 'ledger'])->name('ledger');
            Route::get('trial-balance', [AccountingController::class, 'trialBalance'])->name('trial_balance');
        });

        // Suppliers: bills, payments, and credit notes — all auto-post to
        // the ledger via AccountingService, same as fee payments do.
        Route::middleware('permission:manage_suppliers')->group(function () {
            Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->except(["destroy"]);

            Route::get('suppliers-bills', [\App\Http\Controllers\Admin\SupplierBillController::class, 'index'])->name('suppliers.bills.index');
            Route::get('suppliers-bills/create', [\App\Http\Controllers\Admin\SupplierBillController::class, 'create'])->name('suppliers.bills.create');
            Route::post('suppliers-bills', [\App\Http\Controllers\Admin\SupplierBillController::class, 'store'])->name('suppliers.bills.store');
            Route::get('suppliers-bills/{bill}', [\App\Http\Controllers\Admin\SupplierBillController::class, 'show'])->name('suppliers.bills.show');
            Route::post('suppliers-bills/{bill}/payments', [\App\Http\Controllers\Admin\SupplierBillController::class, 'recordPayment'])->name('suppliers.bills.payments.store');
            Route::post('suppliers-bills/{bill}/credit-notes', [\App\Http\Controllers\Admin\SupplierBillController::class, 'storeCreditNote'])->name('suppliers.bills.credit-notes.store');

            Route::get('finance/vat-report', [\App\Http\Controllers\Admin\VatReportController::class, 'index'])->name('finance.vat_report');
        });

        // Staff Loans & Advances: disbursement posts to the ledger
        // immediately; repayment via payroll is tracked on the loan itself
        // (see PayslipController@generate) since payroll doesn't post to
        // the ledger yet — manual repayments made outside payroll do post.
        Route::middleware('permission:manage_loans')->group(function () {
            Route::get('loans', [\App\Http\Controllers\Admin\StaffLoanController::class, 'index'])->name('loans.index');
            Route::get('loans/create', [\App\Http\Controllers\Admin\StaffLoanController::class, 'create'])->name('loans.create');
            Route::post('loans', [\App\Http\Controllers\Admin\StaffLoanController::class, 'store'])->name('loans.store');
            Route::get('loans/{loan}', [\App\Http\Controllers\Admin\StaffLoanController::class, 'show'])->name('loans.show');
            Route::post('loans/{loan}/repayments', [\App\Http\Controllers\Admin\StaffLoanController::class, 'recordManualRepayment'])->name('loans.repayments.store');
            Route::post('loans/{loan}/write-off', [\App\Http\Controllers\Admin\StaffLoanController::class, 'writeOff'])->name('loans.write-off');
        });

        // Inventory: stationery/consumables POS-style issuing
        Route::middleware('permission:manage_inventory')->group(function () {
            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
            Route::delete('inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
            Route::post('inventory/{item}/issue', [InventoryController::class, 'issue'])->name('inventory.issue');
        });

        // Textbooks: barcode-tracked lending with auto penalty invoicing
        Route::middleware('permission:manage_textbooks')->group(function () {
            Route::get('textbooks', [TextbookController::class, 'index'])->name('textbooks.index');
            Route::post('textbooks/copies', [TextbookController::class, 'storeCopy'])->name('textbooks.copies.store');
            Route::post('textbooks/issue', [TextbookController::class, 'issue'])->name('textbooks.issue');
            Route::post('textbooks/return', [TextbookController::class, 'returnCopy'])->name('textbooks.return');
        });

        // Settings: user rights (permissions) and password resets
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('permission:manage_settings');
        Route::middleware('permission:manage_settings')->group(function () {
            Route::get('settings/school-profile', [SettingsController::class, 'schoolProfile'])->name('settings.school_profile.edit');
            Route::put('settings/school-profile', [SettingsController::class, 'schoolProfileUpdate'])->name('settings.school_profile.update');
        });
        Route::middleware('permission:manage_rights')->group(function () {
            Route::get('settings/rights', [SettingsController::class, 'rightsIndex'])->name('settings.rights.index');
            Route::get('settings/rights/{user}', [SettingsController::class, 'rightsEdit'])->name('settings.rights.edit');
            Route::put('settings/rights/{user}', [SettingsController::class, 'rightsUpdate'])->name('settings.rights.update');
            Route::post('settings/rights/{user}/reset-password', [SettingsController::class, 'resetPassword'])->name('settings.rights.reset_password');
        });
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
        Route::get('/reports', [\App\Http\Controllers\Teacher\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/attendance', [\App\Http\Controllers\Teacher\ReportController::class, 'attendanceSummary'])->name('reports.attendance');
        Route::get('/reports/exam-performance', [\App\Http\Controllers\Teacher\ReportController::class, 'examPerformance'])->name('reports.exam-performance');
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

// SUPER ADMIN — platform-level: onboarding/managing schools themselves, as
// opposed to a single school's own data. Previously defined in
// routes/tenancy.php but never actually merged in, so none of this existed
// in the live app. See App\Http\Middleware\EnsureSuperAdmin.
Route::middleware(['auth', 'super_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::resource('schools', \App\Http\Controllers\SuperAdmin\SchoolController::class);
        Route::post('schools/{school}/toggle-active', [\App\Http\Controllers\SuperAdmin\SchoolController::class, 'toggleActive'])
            ->name('schools.toggle-active');
        Route::post('schools/{school}/impersonate', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'start'])
            ->name('schools.impersonate');
        Route::post('stop-impersonating', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'stop'])
            ->name('stop-impersonating');
    });
