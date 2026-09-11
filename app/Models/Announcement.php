<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ["title", "body", "audience", "school_ids", "level", "is_active", "created_by"];

    protected $casts = [
        "school_ids" => "array",
        "is_active" => "boolean",
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Whether this announcement should be shown to a user belonging to
     * the given school (or with no school at all, e.g. a super_admin).
     */
    public function isForSchool(?int $schoolId): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->audience === "all") {
            return true;
        }

        return $schoolId && in_array($schoolId, $this->school_ids ?? []);
    }
}
