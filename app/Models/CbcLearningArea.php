<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcLearningArea extends Model
{
    use HasFactory;

    protected $fillable = ["name", "school_level", "pathway"];

    public function strands()
    {
        return $this->hasMany(CbcStrand::class);
    }
}
