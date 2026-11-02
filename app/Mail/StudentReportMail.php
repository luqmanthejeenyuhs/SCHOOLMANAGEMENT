<?php

namespace App\Mail;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A learner's attendance / exam results / fee balance, emailed to a
 * parent or guardian. $report is the plain array built by
 * App\Services\GuardianReportService::build().
 *
 * Sent synchronously on purpose (same reasoning as AccountCredentialsMail):
 * it must not silently depend on a queue worker being up.
 */
class StudentReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public School $school,
        public array $report,
        public string $guardianName,
        public ?string $note = null,
    ) {
    }

    public function build()
    {
        $platformDomain = config("school.platform_domain");

        $portalUrl = $platformDomain
            ? "https://{$this->school->slug}.{$platformDomain}/login"
            : route("login.school", $this->school);

        return $this->subject("{$this->school->name} — Progress report for {$this->report['student_name']}")
            ->view("emails.student_report")
            ->with([
                "school" => $this->school,
                "report" => $this->report,
                "guardianName" => $this->guardianName,
                "note" => $this->note,
                "portalUrl" => $portalUrl,
            ]);
    }
}
