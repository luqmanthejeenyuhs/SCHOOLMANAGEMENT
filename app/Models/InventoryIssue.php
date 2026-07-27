<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryIssue extends Model
{
    use HasFactory;

    protected $fillable = ["inventory_item_id", "student_id", "quantity", "billed_to_fee_account", "fee_invoice_id", "issued_by", "issued_at"];

    protected $casts = [
        "billed_to_fee_account" => "boolean",
        "issued_at" => "datetime",
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, "inventory_item_id");
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, "fee_invoice_id");
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, "issued_by");
    }
}
