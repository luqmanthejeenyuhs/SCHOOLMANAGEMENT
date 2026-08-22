<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\StudentCredit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Auto-posts a journal entry for a fee payment: debits the account that
     * matches how the money was received, credits Fees Income for the same
     * amount. This is cash-basis (revenue is recognised when the payment
     * lands, not when the invoice is raised) — matching how invoices
     * currently work, since invoice creation itself isn't posted to the
     * ledger. If you later want accrual accounting (debit Accounts
     * Receivable when an invoice is issued, then credit it here instead of
     * Fees Income), that's a deliberate follow-up, not something to change
     * quietly in this method.
     *
     * Called automatically by App\Observers\PaymentObserver on every
     * Payment::created — covers both the manual "Record Payment" form and
     * the bank/M-Pesa webhook reconciliation path, since both ultimately
     * create a Payment row.
     */
    public function postFeePayment(Payment $payment): JournalEntry
    {
        $schoolId = $payment->school_id;
        $debitAccount = $this->accountForPaymentMethod($schoolId, $payment->method);
        $creditAccount = $this->systemAccount($schoolId, "4000"); // Fees Income

        // Best-effort only: if this runs from a webhook context with no
        // resolved tenant, FeeInvoice's own tenant scope may hide the
        // relation and this just falls back to no student label in the memo
        // — doesn't affect the posted amounts/accounts, which are correct
        // either way since those come from $schoolId directly.
        $studentLabel = optional(optional($payment->invoice)->student)->admission_no;
        $feeTypeLabel = optional(optional($payment->invoice)->feeType)->name;
        $methodDetail = $payment->method;
        if ($payment->bank_name) {
            $methodDetail .= " — {$payment->bank_name}";
        }
        if ($payment->reference) {
            $methodDetail .= " ref: {$payment->reference}";
        }
        $memo = "Fee payment"
            .($studentLabel ? " — {$studentLabel}" : "")
            .($feeTypeLabel ? " [{$feeTypeLabel}]" : "")
            ." ({$methodDetail})";

        return $this->postEntry(
            schoolId: $schoolId,
            date: $payment->payment_date ?? now()->toDateString(),
            memo: $memo,
            lines: [
                ["account_id" => $debitAccount->id, "debit" => (float) $payment->amount_paid, "credit" => 0],
                ["account_id" => $creditAccount->id, "debit" => 0, "credit" => (float) $payment->amount_paid],
            ],
            sourceType: "payment",
            sourceId: $payment->id,
            reference: "PAY-{$payment->id}",
            userId: $payment->received_by,
        );
    }

    /**
     * Overpayment excess that couldn't be applied to any invoice (the
     * student has none outstanding right now) — held as a genuine liability
     * (money received but not yet earned as fee revenue), not credited to
     * Fees Income. Debits the real cash-type account (how the money
     * actually arrived), credits "2000 Student Deposits / Prepaid Fees".
     *
     * The credit gets consumed later via a normal Payment with
     * method="credit_balance" (see accountForPaymentMethod below) — that
     * flips the direction: debits 2000, credits Fees Income, with no new
     * cash involved since it was already received here.
     */
    public function holdAsCredit(int $schoolId, int $studentId, float $amount, string $method, ?string $bankName, ?string $reference, ?int $userId, ?string $studentLabel = null): JournalEntry
    {
        $debitAccount = $this->accountForPaymentMethod($schoolId, $method);
        $creditAccount = $this->systemAccount($schoolId, "2000"); // Student Deposits / Prepaid Fees

        $methodDetail = $method;
        if ($bankName) {
            $methodDetail .= " — {$bankName}";
        }
        if ($reference) {
            $methodDetail .= " ref: {$reference}";
        }

        $memo = "Overpayment held as student credit".($studentLabel ? " — {$studentLabel}" : "")." ({$methodDetail})";

        $entry = $this->postEntry(
            schoolId: $schoolId,
            date: now()->toDateString(),
            memo: $memo,
            lines: [
                ["account_id" => $debitAccount->id, "debit" => $amount, "credit" => 0],
                ["account_id" => $creditAccount->id, "debit" => 0, "credit" => $amount],
            ],
            sourceType: "student_credit",
            reference: "CREDIT-STU-{$studentId}",
            userId: $userId,
        );

        $credit = StudentCredit::allSchools()->firstOrCreate(
            ["school_id" => $schoolId, "student_id" => $studentId],
            ["balance" => 0]
        );
        $credit->increment("balance", $amount);

        return $entry;
    }

    protected function accountForPaymentMethod(int $schoolId, string $method): Account
    {
        $code = match ($method) {
            "cash" => "1000",
            "bank" => "1010",
            "mpesa", "mpesa_c2b" => "1020",
            "credit_balance" => "2000", // applying a student's held credit to an invoice — not new cash
            default => "1030", // card / anything not otherwise mapped
        };

        return $this->systemAccount($schoolId, $code);
    }

    protected function systemAccount(int $schoolId, string $code): Account
    {
        // Uses allSchools() (see Account's BelongsToTenant trait) rather than
        // relying on ambient tenant context: this can run from an M-Pesa/bank
        // webhook, where no Laravel user is authenticated and IdentifyTenant
        // never resolves a tenant, which makes TenantScope fail CLOSED
        // (matches zero rows) rather than open. $schoolId here is always
        // the correct one — it comes straight off the Payment record.
        $account = Account::allSchools()->where("school_id", $schoolId)->where("code", $code)->first();

        if (! $account) {
            throw new InvalidArgumentException(
                "Chart of Accounts is missing system account {$code} for school {$schoolId} — ".
                "run the accounting migrations, or visit Finance > Accounting > Chart of Accounts."
            );
        }

        return $account;
    }

    /**
     * Generic double-entry posting used by both automatic posting above and
     * the manual Journal Entry form. $lines is an array of
     * ["account_id" => int, "debit" => float, "credit" => float]. Throws if
     * the lines don't balance — a journal entry that doesn't balance isn't
     * a valid accounting entry, full stop.
     */
    public function postEntry(int $schoolId, string $date, string $memo, array $lines, string $sourceType = "manual", ?int $sourceId = null, ?string $reference = null, ?int $userId = null): JournalEntry
    {
        $totalDebit = round(array_sum(array_column($lines, "debit")), 2);
        $totalCredit = round(array_sum(array_column($lines, "credit")), 2);

        if ($totalDebit !== $totalCredit) {
            throw new InvalidArgumentException(
                "Journal entry does not balance: total debits ({$totalDebit}) must equal total credits ({$totalCredit})."
            );
        }

        if ($totalDebit <= 0) {
            throw new InvalidArgumentException("Journal entry must have a non-zero amount.");
        }

        return DB::transaction(function () use ($schoolId, $date, $memo, $lines, $sourceType, $sourceId, $reference, $userId) {
            $entry = JournalEntry::create([
                "school_id" => $schoolId,
                "date" => $date,
                "memo" => $memo,
                "reference" => $reference,
                "source_type" => $sourceType,
                "source_id" => $sourceId,
                "created_by" => $userId,
            ]);

            foreach ($lines as $line) {
                if ((float) $line["debit"] > 0 && (float) $line["credit"] > 0) {
                    throw new InvalidArgumentException("A single journal line can't have both a debit and a credit.");
                }

                $entry->lines()->create([
                    "school_id" => $schoolId,
                    "account_id" => $line["account_id"],
                    "debit" => $line["debit"],
                    "credit" => $line["credit"],
                ]);
            }

            return $entry;
        });
    }
}
