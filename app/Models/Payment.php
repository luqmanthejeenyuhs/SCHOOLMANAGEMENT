<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "fee_invoice_id", "amount_paid", "payment_date",
        "method", "bank_name", "reference", "received_by",
    ];

    protected $casts = ["payment_date" => "date"];

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, "fee_invoice_id");
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, "received_by");
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
