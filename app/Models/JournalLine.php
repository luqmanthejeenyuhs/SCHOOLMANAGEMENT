<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "journal_entry_id", "account_id", "debit", "credit"];

    protected $casts = ["debit" => "decimal:2", "credit" => "decimal:2"];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, "journal_entry_id");
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
