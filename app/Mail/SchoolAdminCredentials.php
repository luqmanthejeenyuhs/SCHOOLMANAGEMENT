<?php

namespace App\Mail;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolAdminCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $admin,
        public School $school,
        public string $temporaryPassword,
        public string $loginUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->school->name} admin account on Taaluma SMS",
        );
    }

    public function content(): Content
    {
        return new Content(view: "emails.school-admin-credentials");
    }
}
