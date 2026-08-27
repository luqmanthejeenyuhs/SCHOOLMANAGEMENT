<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

/**
 * Writes an audit_logs row for every login, failed login attempt, and
 * logout — capturing IP address and user agent so "who logged in, and
 * from where" can be answered later. See resources/views/admin/audit/index
 * and App\Http\Controllers\Admin\AuditLogController for where this is read
 * back.
 *
 * Registered as an event subscriber in App\Providers\EventServiceProvider.
 */
class LogAuthenticationActivity
{
    public function __construct(protected AuditLogger $auditLogger)
    {
    }

    public function handleLogin(Login $event): void
    {
        $this->auditLogger->log("login_success", [
            "school_id" => $event->user->school_id ?? null,
            "user_id" => $event->user->id,
            "username_attempted" => $event->user->username,
            "description" => "{$event->user->name} signed in.",
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        // $event->credentials includes the password — never persist that.
        $usernameAttempted = $event->credentials["username"] ?? $event->credentials["email"] ?? null;

        $this->auditLogger->log("login_failed", [
            "school_id" => $event->credentials["school_id"] ?? null,
            "user_id" => $event->user->id ?? null,
            "username_attempted" => $usernameAttempted,
            "description" => "Failed sign-in attempt for username \"{$usernameAttempted}\".",
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $this->auditLogger->log("logout", [
            "school_id" => $event->user->school_id ?? null,
            "user_id" => $event->user->id,
            "username_attempted" => $event->user->username,
            "description" => "{$event->user->name} signed out.",
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [self::class, "handleLogin"]);
        $events->listen(Failed::class, [self::class, "handleFailed"]);
        $events->listen(Logout::class, [self::class, "handleLogout"]);
    }
}
