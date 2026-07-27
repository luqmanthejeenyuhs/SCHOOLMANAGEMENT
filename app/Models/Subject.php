<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ["school_class_id", "name", "code"];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
