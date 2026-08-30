<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "supplier_id", "supplier_bill_id", "amount", "reason", "date", "issued_by"];

    protected $casts = ["date" => "date"];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bill()
    {
        return $this->belongsTo(SupplierBill::class, "supplier_bill_id");
    }

    public function creditNoteNumber(): string
    {
        return "CN-".str_pad((string) $this->id, 6, "0", STR_PAD_LEFT);
    }
}
