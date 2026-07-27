<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    protected $fillable = ["grade", "min_score", "max_score", "points", "remark"];

    protected $casts = [
        "min_score" => "float",
        "max_score" => "float",
        "points" => "float",
    ];

    /**
     * Find the grading band that a percentage score falls into.
     * Bands are cached per-request via a static array since they rarely change
     * and this gets called once per student per exam when rendering results.
     */
    public static function forPercentage(float $percentage): ?self
    {
        static $scales = null;
        $scales ??= self::orderByDesc("min_score")->get();

        return $scales->first(fn ($scale) => $percentage >= $scale->min_score && $percentage <= $scale->max_score);
    }
}
