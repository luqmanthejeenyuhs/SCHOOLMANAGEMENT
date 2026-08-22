<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\StudentCredit;
use App\Services\AccountingService;

class PaymentObserver
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    /**
     * Every Payment — whether created via the admin "Record Payment" form
     * (FeeInvoiceController@recordPayment) or via bank/M-Pesa webhook
     * reconciliation (FinancePostingService@postDeposit) — ends up here,
     * so this is the single place a fee payment turns into a real
     * double-entry journal entry. Neither of those two call sites needs to
     * know accounting exists.
     */
    public function created(Payment $payment): void
    {
        $this->accounting->postFeePayment($payment);

        // method="credit_balance" means this payment was funded from a
        // student's previously-held overpayment credit (see
        // FeeInvoiceController@recordPayment and
        // AccountingService::holdAsCredit) rather than new cash — the
        // journal entry above already correctly debits the liability
        // account, but the fast per-student balance also needs decrementing
        // so it doesn't drift from what the ledger says.
        if ($payment->method === "credit_balance") {
            $studentId = optional($payment->invoice)->student_id;
            if ($studentId) {
                StudentCredit::allSchools()
                    ->where("school_id", $payment->school_id)
                    ->where("student_id", $studentId)
                    ->first()
                    ?->decrement("balance", (float) $payment->amount_paid);
            }
        }
    }
}
