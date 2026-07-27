<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaC2bTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "transaction_id", "msisdn", "bill_ref_number", "amount",
        "student_id", "fee_invoice_id", "payment_id", "status", "raw_payload", "transaction_time",
    ];

    protected $casts = [
        "raw_payload" => "array",
        "transaction_time" => "datetime",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, "fee_invoice_id");
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
