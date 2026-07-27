<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcStrand extends Model
{
    use HasFactory;

    protected $fillable = ["cbc_learning_area_id", "name"];

    public function learningArea()
    {
        return $this->belongsTo(CbcLearningArea::class, "cbc_learning_area_id");
    }

    public function subStrands()
    {
        return $this->hasMany(CbcSubStrand::class);
    }
}
