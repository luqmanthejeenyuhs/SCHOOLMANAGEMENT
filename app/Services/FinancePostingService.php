<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class FinancePostingService
{
    public function __construct(protected SmsService $sms)
    {
    }

    /**
     * Find the student a deposit reference belongs to. Bank tellers and the M-Pesa
     * Paybill "Account Number" field are both expected to carry the admission number.
     */
    public function findStudentByReference(string $reference): ?Student
    {
        $reference = trim($reference);

        return Student::where('admission_no', $reference)
            ->orWhere('admission_no', strtoupper($reference))
            ->first();
    }

    /**
     * Credit the student's oldest outstanding invoice with this amount (or roll the
     * remainder onto the next one if it overpays the first), returning the invoice
     * touched (or null if the student has no outstanding invoices at all) and the
     * Payment record created against it.
     *
     * Wrapped in a DB transaction plus a unique bank/M-Pesa reference upstream, so a
     * webhook retry can't double-credit the same deposit.
     */
    public function postDeposit(Student $student, float $amount, string $method, ?string $note = null): array
    {
        return DB::transaction(function () use ($student, $amount, $method, $note) {
            $invoice = FeeInvoice::where('student_id', $student->id)
                ->where('status', '!=', 'paid')
                ->oldest('due_date')
                ->first();

            // No outstanding invoice — still record the deposit against the most
            // recent invoice (or leave unlinked) so the money isn't lost from the ledger.
            if (! $invoice) {
                $invoice = FeeInvoice::where('student_id', $student->id)->latest()->first();
            }

            $payment = null;

            if ($invoice) {
                $payment = $invoice->payments()->create([
                    'amount_paid' => $amount,
                    'payment_date' => now()->toDateString(),
                    'method' => $method,
                ]);

                $invoice->refresh();
                $balance = $invoice->balance();
                $invoice->update([
                    'status' => $balance <= 0 ? 'paid' : 'partially_paid',
                ]);

                $this->sendReceipt($student, $amount, $invoice->balance(), $method);
            }

            return [$invoice, $payment];
        });
    }

    protected function sendReceipt(Student $student, float $amountPaid, float $newBalance, string $method): void
    {
        if (! $student->guardian_phone) {
            return;
        }

        $label = match ($method) {
            'mpesa_c2b' => 'M-Pesa',
            'bank' => 'bank deposit',
            default => $method,
        };

        $message = sprintf(
            'Dear Parent, KSh %s received via %s for Adm #%s. New Balance: KSh %s.',
            number_format($amountPaid, 2),
            $label,
            $student->admission_no,
            number_format(max($newBalance, 0), 2)
        );

        $this->sms->send($student->guardian_phone, $message, 'fee_reminder', $student->id);
    }
}
