<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'mpesa/callback', // Safaricom Daraja STK push callback
        'mpesa/c2b/validation', // Safaricom Daraja C2B validation ping
        'mpesa/c2b/confirmation', // Safaricom Daraja C2B confirmation
        'webhooks/bank/deposit', // Bank instant-notification webhook (Equity Jenga, Co-op, etc.)
    ];
}
