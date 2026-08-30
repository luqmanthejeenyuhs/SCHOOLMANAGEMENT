<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CreditNote;
use App\Models\JournalEntry;
use App\Models\LoanRepayment;
use App\Models\Payment;
use App\Models\StaffLoan;
use App\Models\SupplierBill;
use App\Models\SupplierBillPayment;
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
        // relation and this just falls back to a plainer memo — doesn't
        // affect the posted amounts/accounts, which are correct either way
        // since those come from $schoolId directly.
        $studentLabel = optional(optional($payment->invoice)->student)->admission_no;
        $feeTypeLabel = optional(optional($payment->invoice)->feeType)->name;
        $methodDetail = $payment->method;
        if ($payment->bank_name) {
            $methodDetail .= " — {$payment->bank_name}";
        }
        if ($payment->reference) {
            $methodDetail .= " ref: {$payment->reference}";
        }
        $memo = ($feeTypeLabel ?: "Fee")." payment".($studentLabel ? " — {$studentLabel}" : "")." ({$methodDetail})";

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

    protected function accountForPaymentMethod(int $schoolId, string $method): Account
    {
        $code = match ($method) {
            "cash" => "1000",
            "bank" => "1010",
            "mpesa", "mpesa_c2b" => "1020",
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
    /**
     * A bill received from a supplier — money the school now owes, on
     * whatever credit terms that supplier gives (see Supplier::payment_terms_days).
     * The VAT portion is split out to VAT Input so it's separately trackable
     * as recoverable input tax, not buried inside the expense figure.
     */
    public function postSupplierBill(SupplierBill $bill): JournalEntry
    {
        $schoolId = $bill->school_id;
        $purchases = $this->accountByCode($schoolId, "5100");
        $vatInput = $this->accountByCode($schoolId, "1300");
        $payable = $this->accountByCode($schoolId, "2000");

        $lines = [
            ["account_id" => $purchases->id, "debit" => $bill->amount, "credit" => 0],
        ];
        if ($bill->vat_amount > 0) {
            $lines[] = ["account_id" => $vatInput->id, "debit" => $bill->vat_amount, "credit" => 0];
        }
        $lines[] = ["account_id" => $payable->id, "debit" => 0, "credit" => $bill->total_amount];

        return $this->postEntry(
            schoolId: $schoolId,
            date: $bill->bill_date->toDateString(),
            memo: "Supplier bill — {$bill->supplier->name}: {$bill->description}",
            lines: $lines,
            sourceType: "supplier_bill",
            sourceId: $bill->id,
            reference: "BILL-{$bill->id}",
        );
    }

    public function postSupplierBillPayment(SupplierBillPayment $payment): JournalEntry
    {
        $bill = $payment->bill;
        $schoolId = $bill->school_id;
        $payable = $this->accountByCode($schoolId, "2000");
        $cashAccount = $this->accountForPaymentMethod($schoolId, $payment->method);

        return $this->postEntry(
            schoolId: $schoolId,
            date: $payment->payment_date->toDateString(),
            memo: "Payment to {$bill->supplier->name} — bill #{$bill->id} ({$payment->method})",
            lines: [
                ["account_id" => $payable->id, "debit" => $payment->amount, "credit" => 0],
                ["account_id" => $cashAccount->id, "debit" => 0, "credit" => $payment->amount],
            ],
            sourceType: "supplier_bill_payment",
            sourceId: $payment->id,
            reference: "SPAY-{$payment->id}",
            userId: $payment->paid_by,
        );
    }

    /**
     * A credit note reduces what's owed to the supplier without any cash
     * changing hands (returned goods, an overcharge correction, etc.) — the
     * mirror image of the original bill posting, reducing both the expense
     * and the payable.
     */
    public function postCreditNote(CreditNote $creditNote): JournalEntry
    {
        $schoolId = $creditNote->school_id;
        $purchases = $this->accountByCode($schoolId, "5100");
        $payable = $this->accountByCode($schoolId, "2000");

        return $this->postEntry(
            schoolId: $schoolId,
            date: $creditNote->date->toDateString(),
            memo: "Credit note — {$creditNote->supplier->name}: {$creditNote->reason}",
            lines: [
                ["account_id" => $payable->id, "debit" => $creditNote->amount, "credit" => 0],
                ["account_id" => $purchases->id, "debit" => 0, "credit" => $creditNote->amount],
            ],
            sourceType: "credit_note",
            sourceId: $creditNote->id,
            reference: $creditNote->creditNoteNumber(),
            userId: $creditNote->issued_by,
        );
    }

    /**
     * Cash actually leaving the school today to hand a staff member their
     * loan/advance — always posted regardless of how it gets repaid.
     */
    public function postLoanDisbursement(StaffLoan $loan, string $method = "bank"): JournalEntry
    {
        $schoolId = $loan->school_id;
        $receivable = $this->accountByCode($schoolId, "1200");
        $cashAccount = $this->accountForPaymentMethod($schoolId, $method);

        return $this->postEntry(
            schoolId: $schoolId,
            date: $loan->start_date->toDateString(),
            memo: "Staff ".($loan->loan_type === "advance" ? "advance" : "loan")." disbursed — {$loan->employee->name}",
            lines: [
                ["account_id" => $receivable->id, "debit" => $loan->principal, "credit" => 0],
                ["account_id" => $cashAccount->id, "debit" => 0, "credit" => $loan->principal],
            ],
            sourceType: "staff_loan",
            sourceId: $loan->id,
            reference: "LOAN-{$loan->id}",
            userId: $loan->approved_by,
        );
    }

    /**
     * Only for a repayment made OUTSIDE payroll (employee pays cash
     * directly) — repayments deducted from a payslip are NOT posted here,
     * since payroll itself doesn't post to the ledger yet in this system
     * (a pre-existing gap). Posting only the loan half of an unposted
     * payroll run would leave the books inconsistent, so those are tracked
     * on the loan/repayment records only until payroll posting is added.
     */
    public function postManualLoanRepayment(LoanRepayment $repayment, string $method = "cash"): JournalEntry
    {
        $loan = $repayment->loan;
        $schoolId = $loan->school_id;
        $receivable = $this->accountByCode($schoolId, "1200");
        $interestIncome = $this->accountByCode($schoolId, "4100");
        $cashAccount = $this->accountForPaymentMethod($schoolId, $method);

        $lines = [
            ["account_id" => $cashAccount->id, "debit" => $repayment->amount, "credit" => 0],
            ["account_id" => $receivable->id, "debit" => 0, "credit" => $repayment->principal_portion],
        ];
        if ($repayment->interest_portion > 0) {
            $lines[] = ["account_id" => $interestIncome->id, "debit" => 0, "credit" => $repayment->interest_portion];
        }

        return $this->postEntry(
            schoolId: $schoolId,
            date: $repayment->payment_date->toDateString(),
            memo: "Loan repayment — {$loan->employee->name}",
            lines: $lines,
            sourceType: "loan_repayment",
            sourceId: $repayment->id,
            reference: "LREPAY-{$repayment->id}",
        );
    }

    protected function accountByCode(int $schoolId, string $code): Account
    {
        return $this->systemAccount($schoolId, $code);
    }

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
