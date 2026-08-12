<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "name", "amount", "frequency"];

    public function invoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }
}
