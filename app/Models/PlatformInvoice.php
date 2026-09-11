<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * What a school owes the platform — deliberately NOT tenant-scoped
 * (no BelongsToTenant trait). Only a super_admin ever queries this;
 * a school admin should never see it, let alone be auto-scoped into it.
 */
class PlatformInvoice extends Model
{
    protected $fillable = [
        "school_id", "amount", "billing_cycle", "due_date", "status",
        "paid_at", "paid_method", "note", "created_by",
    ];

    protected $casts = [
        "due_date" => "date",
        "paid_at" => "datetime",
        "amount" => "decimal:2",
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function isOverdue(): bool
    {
        return $this->status === "pending" && $this->due_date->isPast();
    }
}
