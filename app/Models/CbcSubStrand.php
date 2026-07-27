<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcSubStrand extends Model
{
    use HasFactory;

    protected $fillable = ["cbc_strand_id", "name"];

    public function strand()
    {
        return $this->belongsTo(CbcStrand::class, "cbc_strand_id");
    }

    public function records()
    {
        return $this->hasMany(CbcCompetencyRecord::class);
    }
}
