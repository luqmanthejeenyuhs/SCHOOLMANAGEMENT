<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\MpesaC2bTransaction;
use App\Models\Student;
use App\Services\FinancePostingService;
use Illuminate\Http\Request;

class FinanceLedgerController extends Controller
{
    public function __construct(protected FinancePostingService $posting)
    {
    }

    public function index()
    {
        $bankTransactions = BankTransaction::with(['student.user', 'invoice'])->latest()->paginate(10, ['*'], 'bank_page');
        $mpesaTransactions = MpesaC2bTransaction::with(['student.user', 'invoice'])->latest()->paginate(10, ['*'], 'mpesa_page');

        return view('admin.finance.ledger', compact('bankTransactions', 'mpesaTransactions'));
    }

    /**
     * Manually reconcile an unmatched bank deposit — used when a depositor mistypes
     * the admission number and the automatic webhook match fails.
     */
    public function reconcileBank(Request $request, BankTransaction $bankTransaction)
    {
        $data = $request->validate(['admission_no' => 'required|string|exists:students,admission_no']);
        $student = Student::where('admission_no', $data['admission_no'])->firstOrFail();

        [$invoice, $payment] = $this->posting->postDeposit($student, (float) $bankTransaction->amount, 'bank');

        $bankTransaction->update([
            'student_id' => $student->id,
            'status' => 'matched',
            'fee_invoice_id' => $invoice?->id,
            'payment_id' => $payment?->id,
        ]);

        return back()->with('success', 'Bank deposit reconciled to '.$student->admission_no.'.');
    }

    public function reconcileMpesa(Request $request, MpesaC2bTransaction $mpesaC2bTransaction)
    {
        $data = $request->validate(['admission_no' => 'required|string|exists:students,admission_no']);
        $student = Student::where('admission_no', $data['admission_no'])->firstOrFail();

        [$invoice, $payment] = $this->posting->postDeposit($student, (float) $mpesaC2bTransaction->amount, 'mpesa_c2b');

        $mpesaC2bTransaction->update([
            'student_id' => $student->id,
            'status' => 'matched',
            'fee_invoice_id' => $invoice?->id,
            'payment_id' => $payment?->id,
        ]);

        return back()->with('success', 'M-Pesa payment reconciled to '.$student->admission_no.'.');
    }
}
