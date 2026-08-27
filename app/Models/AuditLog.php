<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An append-only record of security-relevant events — logins (successful
 * and failed), logouts, and anything else routed through
 * App\Services\AuditLogger::log(). Used to answer "who logged in, when,
 * and from what IP address".
 *
 * Deliberately NOT tenant-scoped via the usual BelongsToTenant trait: a
 * failed login before a school is known, or a super_admin action, may have
 * no school_id at all, and admins should only ever see their own school's
 * rows anyway (enforced explicitly in the controller, not a global scope).
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        "school_id", "user_id", "event", "username_attempted",
        "description", "ip_address", "user_agent", "url", "method", "meta",
    ];

    protected $casts = [
        "meta" => "array",
        "created_at" => "datetime",
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
