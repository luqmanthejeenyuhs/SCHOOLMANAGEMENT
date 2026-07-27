<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "fee_invoice_id", "phone", "amount", "merchant_request_id", "checkout_request_id",
        "status", "result_code", "result_desc", "mpesa_receipt_number", "transaction_date",
    ];

    protected $casts = ["transaction_date" => "datetime"];

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, "fee_invoice_id");
    }
}
