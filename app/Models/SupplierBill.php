<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBill extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "supplier_id", "bill_reference", "description",
        "amount", "vat_rate", "vat_amount", "total_amount",
        "bill_date", "due_date", "status", "created_by",
    ];

    protected $casts = ["bill_date" => "date", "due_date" => "date"];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierBillPayment::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum("amount");
    }

    public function totalCredited(): float
    {
        return (float) $this->creditNotes()->sum("amount");
    }

    public function balance(): float
    {
        return round((float) $this->total_amount - $this->totalPaid() - $this->totalCredited(), 2);
    }

    public function isOverdue(): bool
    {
        return $this->status !== "paid" && $this->due_date->isPast();
    }
}
