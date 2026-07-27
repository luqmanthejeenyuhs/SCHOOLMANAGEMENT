<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.africastalking.env') === 'production'
            ? 'https://api.africastalking.com/version1/messaging'
            : 'https://api.sandbox.africastalking.com/version1/messaging';
    }

    /**
     * Send a single SMS and log the attempt. Returns true on success.
     */
    public function send(string $phone, string $message, string $category = 'general', ?int $studentId = null, ?int $sentBy = null): bool
    {
        $apiKey = config('services.africastalking.api_key');
        $username = config('services.africastalking.username');

        // No API key configured yet — log as "queued" so the demo/UI still works end-to-end,
        // and nothing silently disappears once real credentials are added.
        if (! $apiKey) {
            SmsLog::create([
                'student_id' => $studentId,
                'recipient_phone' => $phone,
                'message' => $message,
                'category' => $category,
                'status' => 'queued',
                'provider_response' => 'No AT_API_KEY set in .env yet — message logged but not transmitted.',
                'sent_by' => $sentBy,
            ]);

            return false;
        }

        $response = Http::asForm()->withHeaders([
            'apiKey' => $apiKey,
            'Accept' => 'application/json',
        ])->post($this->baseUrl, array_filter([
            'username' => $username,
            'to' => $phone,
            'message' => $message,
            'from' => config('services.africastalking.sender_id'),
        ]));

        $ok = $response->successful();

        if (! $ok) {
            Log::warning('SMS send failed', ['phone' => $phone, 'body' => $response->body()]);
        }

        SmsLog::create([
            'student_id' => $studentId,
            'recipient_phone' => $phone,
            'message' => $message,
            'category' => $category,
            'status' => $ok ? 'sent' : 'failed',
            'provider_response' => $response->body(),
            'sent_by' => $sentBy,
        ]);

        return $ok;
    }

    /**
     * Send the same message to many recipients (e.g. fee reminders, school closure alerts).
     * Returns [sent_count, failed_count].
     */
    public function sendBulk(array $recipients, string $message, string $category = 'announcement', ?int $sentBy = null): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $ok = $this->send($recipient['phone'], $message, $category, $recipient['student_id'] ?? null, $sentBy);
            $ok ? $sent++ : $failed++;
        }

        return [$sent, $failed];
    }
}
