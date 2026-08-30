<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "student_id", "fee_type_id", "amount", "due_date", "status"];

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

    /**
     * Sums the linked payments. If `payments` was already eager-loaded
     * (e.g. FeeInvoice::with('payments')->get()), this uses that loaded
     * collection and issues NO extra query — calling ->payments()->sum()
     * unconditionally would silently ignore the eager load and fire one
     * fresh query per invoice, which is ruinous across a school-wide list
     * (see App\Http\Controllers\Admin\DashboardController for why this
     * matters: it used to load every invoice for the school on every
     * single dashboard view).
     */
    public function totalPaid(): float
    {
        if ($this->relationLoaded("payments")) {
            return (float) $this->payments->sum("amount_paid");
        }

        return (float) $this->payments()->sum("amount_paid");
    }

    public function balance(): float
    {
        return (float) $this->amount - $this->totalPaid();
    }
}
