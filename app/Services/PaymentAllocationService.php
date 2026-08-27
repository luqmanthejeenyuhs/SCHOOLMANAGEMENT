<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentAllocationService
{
    /**
     * Apply $amount starting at $invoice. If $amount exceeds that invoice's
     * balance, the excess cascades onto the student's next oldest
     * outstanding invoice (by due_date), and so on. If the student runs out
     * of outstanding invoices before the amount is used up, the remainder
     * is applied to the last invoice touched — so it shows up as a visible
     * credit (negative balance) on that invoice rather than being dropped.
     *
     * Each portion is created as its own Payment row against its own
     * invoice, so App\Observers\PaymentObserver fires once per portion —
     * each gets its own correctly-labelled journal entry and its own
     * receipt. That's intentional: a KES 5,000 payment split across Tuition
     * and Transport invoices is genuinely two separate postings.
     *
     * Returns the list of Payment models created (usually one; more if the
     * amount was split across invoices). $attributes carries the fields
     * shared across every split (payment_date, method, bank_name,
     * reference, received_by).
     *
     * @return list<Payment>
     */
    public function apply(FeeInvoice $invoice, float $amount, array $attributes): array
    {
        return DB::transaction(function () use ($invoice, $amount, $attributes) {
            $payments = [];
            $remaining = round($amount, 2);
            $current = $invoice;

            while ($remaining > 0) {
                $balance = $current->balance();
                $next = $this->nextOutstandingInvoice($current);

                // Already settled and there's somewhere else to send the
                // money — skip straight there without creating a $0 payment.
                if ($balance <= 0 && $next) {
                    $current = $next;
                    continue;
                }

                // Cap at this invoice's balance only if there's a further
                // invoice to cascade the rest onto. Otherwise (balance
                // already <= 0, or this is the last outstanding invoice)
                // apply everything left here.
                $portion = ($next && $balance > 0) ? min($remaining, $balance) : $remaining;

                $payments[] = $this->createPayment($current, $portion, $attributes);
                $remaining = round($remaining - $portion, 2);

                if ($remaining > 0) {
                    if (! $next) {
                        break; // nowhere left to put it; already applied above
                    }
                    $current = $next;
                }
            }

            return $payments;
        });
    }

    protected function nextOutstandingInvoice(FeeInvoice $current): ?FeeInvoice
    {
        return FeeInvoice::where('student_id', $current->student_id)
            ->where('id', '!=', $current->id)
            ->where('status', '!=', 'paid')
            ->oldest('due_date')
            ->first();
    }

    protected function createPayment(FeeInvoice $invoice, float $amount, array $attributes): Payment
    {
        $payment = $invoice->payments()->create(array_merge($attributes, [
            'amount_paid' => $amount,
        ]));

        $invoice->refresh();
        $balance = $invoice->balance();
        $invoice->update([
            'status' => $balance <= 0 ? 'paid' : ($balance < $invoice->amount ? 'partially_paid' : 'unpaid'),
        ]);

        return $payment;
    }
}
