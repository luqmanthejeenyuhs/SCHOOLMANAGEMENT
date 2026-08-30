<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "name", "contact_person", "phone", "email",
        "kra_pin", "address", "payment_terms_days", "is_active",
    ];

    protected $casts = ["is_active" => "boolean"];

    public function bills()
    {
        return $this->hasMany(SupplierBill::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function totalOwed(): float
    {
        return (float) $this->bills()->whereIn("status", ["unpaid", "partially_paid"])->get()->sum(fn ($b) => $b->balance());
    }
}
