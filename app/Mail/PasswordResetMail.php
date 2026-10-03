<?php

namespace App\Mail;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when someone uses "Forgot password?" on the login page. $token is
 * the plain reset token — Laravel's password broker generated it and
 * stored only a hashed copy in password_reset_tokens, so this email is the
 * only place the usable link ever exists. Expires per config('auth.
 * passwords.users.expire') (60 minutes by default) — see
 * ResetPasswordController, which re-validates the token via the same
 * broker before allowing a new password to be set.
 */
class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token,
        public ?School $school = null,
    ) {
    }

    public function build()
    {
        $resetUrl = route("password.reset.form", [
            "token" => $this->token,
            "username" => $this->user->username,
            "school" => $this->school?->slug,
        ]);

        return $this->subject(($this->school?->name ?? "Taaluma SMS")." — Reset your password")
            ->view("emails.password_reset")
            ->with([
                "user" => $this->user,
                "school" => $this->school,
                "resetUrl" => $resetUrl,
                "expiryMinutes" => config("auth.passwords.users.expire", 60),
            ]);
    }
}
