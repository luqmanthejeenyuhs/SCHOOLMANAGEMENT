<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    use HasFactory;

    protected $fillable = ["student_id", "fee_type_id", "amount", "due_date", "status"];

    protected $casts = ["due_date" => "date"];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function mpesaTransactions()
    {
        return $this->hasMany(MpesaTransaction::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum("amount_paid");
    }

    public function balance(): float
    {
        return (float) $this->amount - $this->totalPaid();
    }
}
