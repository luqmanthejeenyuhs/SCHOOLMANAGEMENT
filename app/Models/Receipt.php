<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "payment_id", "issued_by"];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, "issued_by");
    }

    /**
     * Human-readable receipt number, derived from the row's own id so it's
     * guaranteed unique and sequential per school without a separate
     * counter column to keep in sync.
     */
    public function receiptNumber(): string
    {
        return "RCT-".str_pad((string) $this->id, 6, "0", STR_PAD_LEFT);
    }
}
