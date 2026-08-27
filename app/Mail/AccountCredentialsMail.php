<?php

namespace App\Mail;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once, right after an admin creates a teacher/student/parent/admin
 * account. Carries the username the admin chose and a freshly generated
 * password — the admin never sees the password itself (see
 * App\Services\AccountProvisioningService), only the account owner does,
 * via this email. They're expected to sign in and change it from
 * /account/password.
 *
 * Sent synchronously (not ShouldQueue) so it doesn't silently depend on a
 * queue worker running on the host — if that ever changes, add ShouldQueue
 * back once a worker (or Horizon/supervisor) is confirmed running.
 */
class AccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
        public ?School $school = null,
    ) {
    }

    public function build()
    {
        $loginUrl = $this->school
            ? route("login.school", $this->school)
            : url("/login");

        return $this->subject(($this->school?->name ?? "Taaluma SMS")." — Your account details")
            ->view("emails.account_credentials")
            ->with([
                "user" => $this->user,
                "plainPassword" => $this->plainPassword,
                "school" => $this->school,
                "loginUrl" => $loginUrl,
            ]);
    }
}
