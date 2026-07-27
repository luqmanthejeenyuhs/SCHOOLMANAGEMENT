<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ["name", "code", "category", "unit_price", "quantity_in_stock", "reorder_level", "description"];

    public function issues()
    {
        return $this->hasMany(InventoryIssue::class);
    }

    public function textbookCopies()
    {
        return $this->hasMany(TextbookCopy::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }
}
