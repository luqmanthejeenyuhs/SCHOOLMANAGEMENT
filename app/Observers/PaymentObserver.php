<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Receipt;
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
     * so this is the single place a fee payment turns into both a real
     * double-entry journal entry AND an official receipt. Neither call
     * site needs to know accounting or receipts exist.
     */
    public function created(Payment $payment): void
    {
        $this->accounting->postFeePayment($payment);

        // school_id is taken from the payment directly (not ambient tenant
        // context) for the same reason AccountingService does this: this can
        // run from a webhook request where no tenant has been resolved.
        Receipt::create([
            "school_id" => $payment->school_id,
            "payment_id" => $payment->id,
            "issued_by" => $payment->received_by,
        ]);
    }
}
