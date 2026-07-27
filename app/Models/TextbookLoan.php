<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextbookLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        "textbook_copy_id", "student_id", "issued_by", "issued_at", "due_date",
        "condition_at_issue", "returned_at", "condition_at_return", "status", "penalty_invoice_id",
    ];

    protected $casts = [
        "issued_at" => "datetime",
        "due_date" => "date",
        "returned_at" => "datetime",
    ];

    public function copy()
    {
        return $this->belongsTo(TextbookCopy::class, "textbook_copy_id");
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, "issued_by");
    }

    public function penaltyInvoice()
    {
        return $this->belongsTo(FeeInvoice::class, "penalty_invoice_id");
    }
}
