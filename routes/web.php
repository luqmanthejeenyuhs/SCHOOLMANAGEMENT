<?php

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
use App\Http\Controllers\Admin\MpesaController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\LoanRequestController;
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
use App\Http\Controllers\Teacher\LeaveController;
use App\Http\Controllers\Teacher\LoanController;
use App\Http\Controllers\Teacher\PayslipController as StaffPayslipController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ExamResultController as TeacherExamResultController;
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
    // Generic login — no school branding, used for the platform super_admin.
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Branded, per-school login — shows that school's logo/name and scopes
    // the username lookup to it. This is the link a school shares with its
    // own staff/students/parents (e.g. /school/greenwood/login).
    Route::get('/school/{school:slug}/login', [LoginController::class, 'showLoginForm'])->name('login.school');
    Route::post('/school/{school:slug}/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    // Self-service password change — every signed-in user (any role) can
    // change their own password here without an admin seeing the new value.
    Route::get('/account/password', [\App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('account.password.edit');
    Route::post('/announcements/{announcement}/dismiss', function (\App\Models\Announcement $announcement) {
        $dismissed = session('dismissed_announcements', []);
        $dismissed[] = $announcement->id;
        session(['dismissed_announcements' => array_unique($dismissed)]);

        return response()->noContent();
    })->name('announcements.dismiss');
    Route::put('/account/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('account.password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Self clock-in, open to anyone with a linked Employee record —
    // teachers already have /teacher/clock; this is the same controller so
    // non-teaching staff who log in with an admin-role account (bursar,
    // front office, etc.) can clock themselves in too.
    Route::prefix('staff')->name('staff.')->middleware('role:admin,teacher')->group(function () {
        Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
        Route::post('/clock', [ClockController::class, 'store'])->name('clock.store');

        Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');

        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');

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
        Route::middleware('permission:manage_staff_attendance')->group(function () {
            Route::post('staff-attendance', [StaffAttendanceController::class, 'store'])->name('staff_attendance.store');
            Route::put('staff-attendance/{staffAttendance}', [StaffAttendanceController::class, 'update'])->name('staff_attendance.update');
        });

        Route::middleware('permission:manage_leave_requests')->group(function () {
            Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
            Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave_requests.approve');
            Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave_requests.reject');
        });

        Route::middleware('permission:manage_loan_requests')->group(function () {
            Route::get('loan-requests', [LoanRequestController::class, 'index'])->name('loan_requests.index');
            Route::post('loan-requests/{loanRequest}/approve', [LoanRequestController::class, 'approve'])->name('loan_requests.approve');
            Route::post('loan-requests/{loanRequest}/reject', [LoanRequestController::class, 'reject'])->name('loan_requests.reject');
            Route::post('loan-requests/{loanRequest}/disburse', [LoanRequestController::class, 'markDisbursed'])->name('loan_requests.disburse');
        });

        // Recurring deductions (SACCO, union dues, etc.) that apply to
        // payslips automatically — separate from staff loan repayments below.
        Route::middleware('permission:manage_employees')->group(function () {
            Route::get('deduction-types', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'index'])->name('deduction-types.index');
            Route::post('deduction-types', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'store'])->name('deduction-types.store');
            Route::put('deduction-types/{deductionType}', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'update'])->name('deduction-types.update');
            Route::delete('deduction-types/{deductionType}', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'destroy'])->name('deduction-types.destroy');
            Route::post('deduction-types/assign', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'assign'])->name('deduction-types.assign');
            Route::delete('employee-deductions/{employeeDeduction}', [\App\Http\Controllers\Admin\DeductionTypeController::class, 'unassign'])->name('employee-deductions.unassign');
        });

        // Ledger-integrated staff loans — disbursement posts a real journal
        // entry and repayments deduct from payroll automatically. Distinct
        // from the simpler loan-requests approve/reject flow above.
        Route::middleware('permission:manage_loans')->group(function () {
            Route::get('loans', [\App\Http\Controllers\Admin\StaffLoanController::class, 'index'])->name('loans.index');
            Route::get('loans/create', [\App\Http\Controllers\Admin\StaffLoanController::class, 'create'])->name('loans.create');
            Route::post('loans', [\App\Http\Controllers\Admin\StaffLoanController::class, 'store'])->name('loans.store');
            Route::get('loans/{loan}', [\App\Http\Controllers\Admin\StaffLoanController::class, 'show'])->name('loans.show');
            Route::post('loans/{loan}/repayments', [\App\Http\Controllers\Admin\StaffLoanController::class, 'recordManualRepayment'])->name('loans.repayments.store');
            Route::post('loans/{loan}/write-off', [\App\Http\Controllers\Admin\StaffLoanController::class, 'writeOff'])->name('loans.write-off');
        });

        // Suppliers, their bills, and VAT reporting on supplier purchases
        // (school fees themselves are VAT-exempt — see config/vat.php).
        Route::middleware('permission:manage_suppliers')->group(function () {
            Route::get('suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('suppliers.index');
            Route::get('suppliers/create', [\App\Http\Controllers\Admin\SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('suppliers.store');
            Route::get('suppliers/{supplier}/edit', [\App\Http\Controllers\Admin\SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('suppliers/{supplier}', [\App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('suppliers.update');
            Route::get('suppliers/{supplier}', [\App\Http\Controllers\Admin\SupplierController::class, 'show'])->name('suppliers.show');

            Route::get('supplier-bills', [\App\Http\Controllers\Admin\SupplierBillController::class, 'index'])->name('suppliers.bills.index');
            Route::get('supplier-bills/create', [\App\Http\Controllers\Admin\SupplierBillController::class, 'create'])->name('suppliers.bills.create');
            Route::post('supplier-bills', [\App\Http\Controllers\Admin\SupplierBillController::class, 'store'])->name('suppliers.bills.store');
            Route::get('supplier-bills/{bill}', [\App\Http\Controllers\Admin\SupplierBillController::class, 'show'])->name('suppliers.bills.show');
            Route::post('supplier-bills/{bill}/payments', [\App\Http\Controllers\Admin\SupplierBillController::class, 'recordPayment'])->name('suppliers.bills.payments.store');
            Route::post('supplier-bills/{bill}/credit-notes', [\App\Http\Controllers\Admin\SupplierBillController::class, 'storeCreditNote'])->name('suppliers.bills.credit-notes.store');

            Route::get('vat-report', [\App\Http\Controllers\Admin\VatReportController::class, 'index'])->name('vat-report.index');
        });

        // Receipts — one auto-generated per payment (see App\Observers\PaymentObserver).
        Route::middleware('permission:record_payments')->group(function () {
            Route::get('receipts', [\App\Http\Controllers\Admin\ReceiptController::class, 'index'])->name('receipts.index');
            Route::get('receipts/{receipt}', [\App\Http\Controllers\Admin\ReceiptController::class, 'show'])->name('receipts.show');
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
        Route::middleware('permission:view_audit_logs')->group(function () {
            Route::get('settings/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('settings.audit_logs.index');
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
        Route::get('/subjects', [\App\Http\Controllers\Teacher\SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/{subject}', [\App\Http\Controllers\Teacher\SubjectController::class, 'show'])->name('subjects.show');
    });

    // STUDENT
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/results', [\App\Http\Controllers\Student\PortalController::class, 'results'])->name('results.index');
        Route::get('/fees', [\App\Http\Controllers\Student\PortalController::class, 'fees'])->name('fees.index');
        Route::get('/class', [\App\Http\Controllers\Student\PortalController::class, 'myClass'])->name('class.index');
        Route::get('/activities', [\App\Http\Controllers\Student\PortalController::class, 'activities'])->name('activities.index');
        Route::get('/teachers', [\App\Http\Controllers\Student\PortalController::class, 'teachers'])->name('teachers.index');
        Route::get('/performance', [\App\Http\Controllers\Student\PortalController::class, 'performance'])->name('performance.index');
        Route::get('/library', [\App\Http\Controllers\Student\PortalController::class, 'library'])->name('library.index');
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
// opposed to a single school's own data. See App\Http\Middleware\EnsureSuperAdmin.
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

        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('billing', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'index'])->name('billing.index');
        Route::get('billing/create', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'create'])->name('billing.create');
        Route::post('billing', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'store'])->name('billing.store');
        Route::post('billing/{invoice}/mark-paid', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'markPaid'])->name('billing.mark-paid');
        Route::delete('billing/{invoice}', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'destroy'])->name('billing.destroy');

        Route::get('audit-logs', [\App\Http\Controllers\SuperAdmin\AuditLogController::class, 'index'])->name('audit_logs.index');

        Route::get('users', [\App\Http\Controllers\SuperAdmin\UserLookupController::class, 'index'])->name('users.index');

        Route::get('announcements', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/create', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('announcements', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'store'])->name('announcements.store');
        Route::post('announcements/{announcement}/toggle-active', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'toggleActive'])->name('announcements.toggle-active');
        Route::delete('announcements/{announcement}', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });
