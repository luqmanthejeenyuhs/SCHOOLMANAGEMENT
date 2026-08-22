<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "date", "memo", "reference", "source_type", "source_id", "created_by"];

    protected $casts = ["date" => "date"];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function totalDebit(): float
    {
        return (float) $this->lines->sum("debit");
    }

    public function totalCredit(): float
    {
        return (float) $this->lines->sum("credit");
    }

    public function isBalanced(): bool
    {
        return round($this->totalDebit() - $this->totalCredit(), 2) === 0.0;
    }
}
