<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Facades\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "grade", "min_score", "max_score", "points", "remark"];

    protected $casts = [
        "min_score" => "float",
        "max_score" => "float",
        "points" => "float",
    ];

    /**
     * Find the grading band that a percentage score falls into.
     * Bands are cached per-request via a static array since they rarely change
     * and this gets called once per student per exam when rendering results.
     * Keyed by school so a long-running worker switching tenants (via
     * Tenant::runFor()) can't serve one school's grading bands to another.
     */
    public static function forPercentage(float $percentage): ?self
    {
        static $scalesBySchool = [];

        $key = Tenant::id() ?? "none";
        $scalesBySchool[$key] ??= self::orderByDesc("min_score")->get();

        return $scalesBySchool[$key]->first(fn ($scale) => $percentage >= $scale->min_score && $percentage <= $scale->max_score);
    }
}
