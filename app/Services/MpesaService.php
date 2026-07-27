<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('mpesa.base_url');
    }

    /**
     * Get an OAuth access token from Daraja.
     */
    public function getAccessToken(): ?string
    {
        $response = Http::withBasicAuth(
            config('mpesa.consumer_key'),
            config('mpesa.consumer_secret')
        )->get("{$this->baseUrl}/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            Log::error('M-Pesa auth failed', ['body' => $response->body()]);

            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Trigger an STK Push (Lipa na M-Pesa Online) prompt to the payer's phone.
     *
     * @param  string  $phone  Format: 2547XXXXXXXX
     * @param  int  $amount  Whole-number KES amount (Daraja sandbox does not accept decimals)
     * @param  string  $accountReference  e.g. invoice number / admission number
     * @param  string  $description  Short transaction description
     */
    public function stkPush(string $phone, int $amount, string $accountReference, string $description): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return ['success' => false, 'message' => 'Could not authenticate with M-Pesa. Check your Daraja credentials in .env.'];
        }

        $shortcode = config('mpesa.shortcode');
        $passkey = config('mpesa.passkey');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        $response = Http::withToken($token)->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => config('mpesa.transaction_type'),
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => config('mpesa.callback_url'),
            'AccountReference' => $accountReference,
            'TransactionDesc' => $description,
        ]);

        $body = $response->json();

        if ($response->failed() || ($body['ResponseCode'] ?? null) !== '0') {
            Log::warning('M-Pesa STK push not accepted', ['body' => $body]);

            return [
                'success' => false,
                'message' => $body['errorMessage'] ?? $body['ResponseDescription'] ?? 'STK push request was rejected.',
            ];
        }

        return [
            'success' => true,
            'merchant_request_id' => $body['MerchantRequestID'] ?? null,
            'checkout_request_id' => $body['CheckoutRequestID'] ?? null,
            'message' => 'STK push sent. Ask the payer to check their phone and enter their M-Pesa PIN.',
        ];
    }
}
