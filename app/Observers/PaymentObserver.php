<?php

namespace App\Observers;

use App\Models\Payment;
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
    }
}
