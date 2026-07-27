<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Services\FinancePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankWebhookController extends Controller
{
    public function __construct(protected FinancePostingService $posting)
    {
    }

    /**
     * Generic instant-notification receiver for bank deposit/cheque webhooks
     * (Equity Bank Jenga API, Co-op Bank instant notifications, etc). Field names
     * below are the common denominator across these APIs — map your bank's exact
     * payload keys here once you have real webhook credentials from them.
     *
     * Expected payload (adjust to match your bank's actual schema):
     * {
     *   "bank_name": "Equity Bank",
     *   "reference": "FT23A1B2C3",          // bank's unique transaction reference
     *   "account_reference": "ADM-0001",     // what the depositor typed as reference
     *   "amount": 15000.00,
     *   "transaction_date": "2026-07-25T10:15:00Z"
     * }
     */
    public function handle(Request $request)
    {
        Log::info('Bank deposit webhook received', $request->all());

        $data = $request->validate([
            'bank_name' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'account_reference' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'nullable|date',
        ]);

        // Idempotency: a retried webhook for a reference we've already processed
        // must not double-credit the account.
        if (BankTransaction::where('bank_reference', $data['reference'])->exists()) {
            return response()->json(['status' => 'duplicate', 'message' => 'Already processed.']);
        }

        $student = $this->posting->findStudentByReference($data['account_reference']);

        $bankTransaction = BankTransaction::create([
            'bank_name' => $data['bank_name'],
            'bank_reference' => $data['reference'],
            'account_reference' => $data['account_reference'],
            'amount' => $data['amount'],
            'student_id' => $student?->id,
            'status' => 'unmatched',
            'raw_payload' => $request->all(),
            'deposited_at' => $data['transaction_date'] ?? now(),
        ]);

        if ($student) {
            [$invoice, $payment] = $this->posting->postDeposit($student, (float) $data['amount'], 'bank');

            $bankTransaction->update([
                'status' => 'matched',
                'fee_invoice_id' => $invoice?->id,
                'payment_id' => $payment?->id,
            ]);
        }

        return response()->json(['status' => 'ok', 'matched' => (bool) $student]);
    }
}
