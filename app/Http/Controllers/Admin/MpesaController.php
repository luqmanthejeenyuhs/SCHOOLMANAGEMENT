<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function __construct(protected MpesaService $mpesa, protected SmsService $sms)
    {
    }

    /**
     * Trigger an STK push prompt for a given invoice (admin dashboard "Pay via M-Pesa" button).
     */
    public function push(Request $request, FeeInvoice $invoice)
    {
        $data = $request->validate([
            "phone" => "required|string|regex:/^2547[0-9]{8}$/",
        ]);

        $balance = (int) ceil($invoice->balance());

        $result = $this->mpesa->stkPush(
            $data["phone"],
            $balance,
            $invoice->student->admission_no,
            "School fee payment - Invoice #".$invoice->id
        );

        if ($result["success"]) {
            MpesaTransaction::create([
                "fee_invoice_id" => $invoice->id,
                "phone" => $data["phone"],
                "amount" => $balance,
                "merchant_request_id" => $result["merchant_request_id"],
                "checkout_request_id" => $result["checkout_request_id"],
                "status" => "pending",
            ]);
        }

        return back()->with($result["success"] ? "success" : "error", $result["message"]);
    }

    /**
     * Safaricom Daraja callback endpoint. Public route, no auth/CSRF — Safaricom calls this directly.
     */
    public function callback(Request $request)
    {
        Log::info("M-Pesa callback received", $request->all());

        $body = $request->input("Body.stkCallback");
        if (! $body) {
            return response()->json(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
        }

        $checkoutRequestId = $body["CheckoutRequestID"] ?? null;
        $resultCode = $body["ResultCode"] ?? null;
        $resultDesc = $body["ResultDesc"] ?? null;

        $transaction = MpesaTransaction::where("checkout_request_id", $checkoutRequestId)->first();

        if ($transaction) {
            $transaction->update([
                "status" => $resultCode == 0 ? "success" : "failed",
                "result_code" => $resultCode,
                "result_desc" => $resultDesc,
            ]);

            if ($resultCode == 0) {
                $items = collect($body["CallbackMetadata"]["Item"] ?? []);
                $receipt = data_get($items->firstWhere("Name", "MpesaReceiptNumber"), "Value");
                $amount = data_get($items->firstWhere("Name", "Amount"), "Value") ?? $transaction->amount;
                $transDate = data_get($items->firstWhere("Name", "TransactionDate"), "Value");

                $transaction->update([
                    "mpesa_receipt_number" => $receipt,
                    "transaction_date" => $transDate ? \Carbon\Carbon::createFromFormat("YmdHis", (string) $transDate) : now(),
                ]);

                $invoice = $transaction->invoice;
                $invoice->payments()->create([
                    "amount_paid" => $amount,
                    "payment_date" => now()->toDateString(),
                    "method" => "mpesa",
                ]);

                $invoice->refresh();
                $balance = $invoice->balance();
                $invoice->update([
                    "status" => $balance <= 0 ? "paid" : "partially_paid",
                ]);

                $studentName = $invoice->student->name ?? "your child";
                $message = "Payment received: KES ".number_format((float) $amount, 2).
                    " for {$studentName} (Invoice #{$invoice->id}). Receipt: {$receipt}. ".
                    "Balance: KES ".number_format($balance, 2).". - School Management System";

                $this->sms->send(
                    phone: $transaction->phone,
                    message: $message,
                    category: "payment_confirmation",
                    studentId: $invoice->student_id ?? null
                );
            }
        }

        // Safaricom expects this exact acknowledgement shape
        return response()->json(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
    }

    /**
     * Lightweight polling endpoint the invoice modal can call to check if payment landed yet.
     */
    public function status(MpesaTransaction $transaction)
    {
        return response()->json(["status" => $transaction->status, "receipt" => $transaction->mpesa_receipt_number]);
    }
}