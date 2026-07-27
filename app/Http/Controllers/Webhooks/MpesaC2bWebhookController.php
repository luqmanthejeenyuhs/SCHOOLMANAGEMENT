<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\MpesaC2bTransaction;
use App\Services\FinancePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaC2bWebhookController extends Controller
{
    public function __construct(protected FinancePostingService $posting)
    {
    }

    /**
     * Safaricom calls this first (if you registered a Validation URL on Daraja) to ask
     * "should I accept this payment?" before it finalizes. We accept everything here —
     * reference matching happens at confirmation time — but you could reject unknown
     * account numbers at this stage instead if you want stricter control.
     */
    public function validation(Request $request)
    {
        Log::info('M-Pesa C2B validation ping', $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Safaricom calls this once the C2B Paybill payment has actually gone through.
     * Daraja's real payload uses PascalCase keys — TransID, TransAmount, BillRefNumber,
     * MSISDN, TransTime — mapped below.
     */
    public function confirmation(Request $request)
    {
        Log::info('M-Pesa C2B confirmation received', $request->all());

        $transId = $request->input('TransID');
        $amount = (float) $request->input('TransAmount', 0);
        $billRef = $request->input('BillRefNumber');
        $msisdn = $request->input('MSISDN');
        $transTime = $request->input('TransTime'); // format: YmdHis

        if (! $transId || ! $billRef || $amount <= 0) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // Idempotency: Safaricom may retry a confirmation callback.
        if (MpesaC2bTransaction::where('transaction_id', $transId)->exists()) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $student = $this->posting->findStudentByReference($billRef);

        $transaction = MpesaC2bTransaction::create([
            'transaction_id' => $transId,
            'msisdn' => $msisdn,
            'bill_ref_number' => $billRef,
            'amount' => $amount,
            'student_id' => $student?->id,
            'status' => 'unmatched',
            'raw_payload' => $request->all(),
            'transaction_time' => $transTime ? \Carbon\Carbon::createFromFormat('YmdHis', (string) $transTime) : now(),
        ]);

        if ($student) {
            [$invoice, $payment] = $this->posting->postDeposit($student, $amount, 'mpesa_c2b');

            $transaction->update([
                'status' => 'matched',
                'fee_invoice_id' => $invoice?->id,
                'payment_id' => $payment?->id,
            ]);
        }

        // Safaricom expects exactly this acknowledgement shape, regardless of match outcome
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
