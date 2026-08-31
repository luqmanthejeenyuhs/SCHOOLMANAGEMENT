<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "payslip_id", "label", "category", "amount"];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
