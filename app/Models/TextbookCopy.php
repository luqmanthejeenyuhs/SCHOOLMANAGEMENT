<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextbookCopy extends Model
{
    use HasFactory;

    protected $fillable = ["inventory_item_id", "barcode", "condition", "status", "current_student_id"];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, "inventory_item_id");
    }

    public function currentStudent()
    {
        return $this->belongsTo(Student::class, "current_student_id");
    }

    public function loans()
    {
        return $this->hasMany(TextbookLoan::class);
    }
}
