<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "bank_name", "bank_reference", "account_reference", "amount",
        "student_id", "fee_invoice_id", "payment_id", "status", "raw_payload", "deposited_at",
    ];

    protected $casts = [
        "raw_payload" => "array",
        "deposited_at" => "datetime",
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
