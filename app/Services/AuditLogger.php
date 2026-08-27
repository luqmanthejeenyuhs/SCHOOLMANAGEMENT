<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Single entry point for writing to the audit trail. Call
 * app(AuditLogger::class)->log(...) or resolve it via the container —
 * see App\Listeners\LogAuthenticationActivity for the main caller.
 */
class AuditLogger
{
    public function __construct(protected Request $request)
    {
    }

    public function log(string $event, array $options = []): AuditLog
    {
        return AuditLog::create([
            "school_id" => $options["school_id"] ?? null,
            "user_id" => $options["user_id"] ?? null,
            "event" => $event,
            "username_attempted" => $options["username_attempted"] ?? null,
            "description" => $options["description"] ?? null,
            "ip_address" => $this->request->ip(),
            "user_agent" => (string) $this->request->userAgent(),
            "url" => $this->request->fullUrl(),
            "method" => $this->request->method(),
            "meta" => $options["meta"] ?? null,
            "created_at" => now(),
        ]);
    }
}
