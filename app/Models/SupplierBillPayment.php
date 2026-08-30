<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBillPayment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "supplier_bill_id", "amount", "payment_date", "method", "reference", "paid_by"];

    protected $casts = ["payment_date" => "date"];

    public function bill()
    {
        return $this->belongsTo(SupplierBill::class, "supplier_bill_id");
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, "paid_by");
    }
}
