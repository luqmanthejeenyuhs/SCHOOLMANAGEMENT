<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FeeInvoice;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class AccountingController extends Controller
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    // ---------- Finance Overview ----------

    public function overview()
    {
        $accounts = Account::orderBy("code")->get();
        $cashPosition = $accounts->whereIn("code", ["1000", "1010", "1020", "1030"])->sum(fn ($a) => $a->balance());

        $allInvoices = FeeInvoice::with("payments")->get();
        $stats = [
            "cash_position" => $cashPosition,
            "collected" => (float) $allInvoices->sum->totalPaid(),
            "outstanding" => (float) $allInvoices->sum->balance(),
            "unpaid_invoices" => FeeInvoice::where("status", "!=", "paid")->count(),
        ];

        return view("admin.accounting.overview", compact("stats"));
    }

    // ---------- Chart of Accounts ----------

    public function chartOfAccounts()
    {
        $accounts = Account::orderBy("code")->get();

        return view("admin.accounting.chart_of_accounts", compact("accounts"));
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            "code" => ["required", "string", "max:20", Rule::unique("accounts", "code")->where("school_id", Tenant::id())],
            "name" => "required|string|max:255",
            "type" => ["required", Rule::in(Account::TYPES)],
        ]);

        Account::create($data);

        return back()->with("success", "Account added to the Chart of Accounts.");
    }

    // ---------- Journal Entries ----------

    public function journalEntries()
    {
        $entries = JournalEntry::with(["lines.account", "createdBy"])->latest("date")->latest("id")->paginate(15);
        $accounts = Account::orderBy("code")->get();

        return view("admin.accounting.journal_entries", compact("entries", "accounts"));
    }

    public function storeJournalEntry(Request $request)
    {
        $data = $request->validate([
            "date" => "required|date",
            "memo" => "required|string|max:255",
            "reference" => "nullable|string|max:255",
            "account_id" => "required|array|min:2",
            "account_id.*" => [Rule::exists("accounts", "id")->where("school_id", Tenant::id())],
            "debit" => "required|array",
            "debit.*" => "nullable|numeric|min:0",
            "credit" => "required|array",
            "credit.*" => "nullable|numeric|min:0",
        ]);

        $lines = [];
        foreach ($data["account_id"] as $i => $accountId) {
            $debit = (float) ($data["debit"][$i] ?? 0);
            $credit = (float) ($data["credit"][$i] ?? 0);
            if ($debit == 0 && $credit == 0) {
                continue; // skip blank rows the form always has a spare of
            }
            $lines[] = ["account_id" => $accountId, "debit" => $debit, "credit" => $credit];
        }

        try {
            $this->accounting->postEntry(
                schoolId: Tenant::id(),
                date: $data["date"],
                memo: $data["memo"],
                lines: $lines,
                sourceType: "manual",
                reference: $data["reference"] ?? null,
                userId: $request->user()->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with("error", $e->getMessage());
        }

        return redirect()->route("admin.accounting.journal_entries")->with("success", "Journal entry posted.");
    }

    // ---------- General Ledger ----------

    public function ledger(Request $request)
    {
        $accounts = Account::orderBy("code")->get();

        $selectedAccountId = $request->integer("account_id") ?: optional($accounts->first())->id;
        $selectedAccount = $accounts->firstWhere("id", $selectedAccountId);

        $lines = collect();
        $runningBalance = 0.0;

        if ($selectedAccount) {
            $lines = $selectedAccount->lines()
                ->with("entry")
                ->get()
                ->sortBy([["entry.date", "asc"], ["id", "asc"]])
                ->map(function ($line) use ($selectedAccount, &$runningBalance) {
                    $movement = $selectedAccount->isDebitNormal()
                        ? (float) $line->debit - (float) $line->credit
                        : (float) $line->credit - (float) $line->debit;
                    $runningBalance += $movement;
                    $line->running_balance = $runningBalance;

                    return $line;
                });
        }

        return view("admin.accounting.ledger", compact("accounts", "selectedAccount", "lines"));
    }

    // ---------- Trial Balance ----------

    public function trialBalance()
    {
        $accounts = Account::orderBy("code")->get()->map(function (Account $account) {
            $balance = $account->balance(); // signed: positive = this account's normal direction
            $normalIsDebit = $account->isDebitNormal();
            $debit = 0.0;
            $credit = 0.0;

            if ($balance >= 0) {
                $normalIsDebit ? $debit = $balance : $credit = $balance;
            } else {
                // Abnormal balance (rare, e.g. a refund larger than income
                // received) — still needs to land in a trial balance column.
                $normalIsDebit ? $credit = -$balance : $debit = -$balance;
            }

            return ["account" => $account, "debit" => $debit, "credit" => $credit];
        });

        $totalDebit = round($accounts->sum("debit"), 2);
        $totalCredit = round($accounts->sum("credit"), 2);

        return view("admin.accounting.trial_balance", compact("accounts", "totalDebit", "totalCredit"));
    }
}
