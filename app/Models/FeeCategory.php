<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A grade grouping that shares one fee schedule — e.g. "Lower Primary"
 * (Grade 1-3). Each school defines its own via Admin\FeeCategoryController.
 */
class FeeCategory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "name"];

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function feeTypes()
    {
        return $this->hasMany(FeeType::class);
    }
}
