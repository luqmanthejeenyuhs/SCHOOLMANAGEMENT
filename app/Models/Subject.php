<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "school_class_id", "name", "code"];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
