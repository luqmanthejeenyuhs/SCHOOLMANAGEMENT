<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class FinancePostingService
{
    public function __construct(protected SmsService $sms, protected PaymentAllocationService $allocation)
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
     * Credit the student's oldest outstanding invoice with this amount, cascading any
     * excess onto their next outstanding invoice(s) via PaymentAllocationService (or
     * applying it as a credit to the last invoice touched if none remain). Returns
     * the first invoice touched (or null if the student has no invoices at all) and
     * the first Payment record created against it — matching the shape every caller
     * (FinanceLedgerController, the bank/M-Pesa webhooks) already expects, since a
     * BankTransaction/MpesaC2bTransaction row only has a single payment_id column.
     * If the amount cascaded into more than one Payment, the rest are still created
     * and posted/receipted normally (via PaymentObserver) — they're just not the one
     * returned here.
     *
     * Wrapped in a DB transaction plus a unique bank/M-Pesa reference upstream, so a
     * webhook retry can't double-credit the same deposit.
     */
    public function postDeposit(Student $student, float $amount, string $method, ?string $note = null): array
    {
        return DB::transaction(function () use ($student, $amount, $method) {
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
                $payments = $this->allocation->apply($invoice, $amount, [
                    'payment_date' => now()->toDateString(),
                    'method' => $method,
                ]);
                $payment = $payments[0] ?? null;

                $lastInvoice = ! empty($payments) ? end($payments)->invoice : $invoice;
                $this->sendReceipt($student, $amount, $lastInvoice->balance(), $method);
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
