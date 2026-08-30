<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "staff_loan_id", "payslip_id", "amount", "principal_portion", "interest_portion", "payment_date"];

    protected $casts = ["payment_date" => "date"];

    public function loan()
    {
        return $this->belongsTo(StaffLoan::class, "staff_loan_id");
    }

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
