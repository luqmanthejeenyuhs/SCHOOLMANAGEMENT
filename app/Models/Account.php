<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "code", "name", "type", "is_system"];

    protected $casts = ["is_system" => "boolean"];

    public const TYPES = ["asset", "liability", "equity", "income", "expense"];

    /**
     * The standard starting Chart of Accounts every school gets automatically
     * (see App\Observers\SchoolObserver). "is_system" accounts are the ones
     * AccountingService::postFeePayment() posts to by code — don't delete or
     * repurpose those codes without also updating that mapping.
     */
    public static function defaultChart(): array
    {
        return [
            ["code" => "1000", "name" => "Cash", "type" => "asset", "is_system" => true],
            ["code" => "1010", "name" => "Bank", "type" => "asset", "is_system" => true],
            ["code" => "1020", "name" => "M-Pesa", "type" => "asset", "is_system" => true],
            ["code" => "1030", "name" => "Other Receipts (Card/Unmapped)", "type" => "asset", "is_system" => true],
            ["code" => "1100", "name" => "Accounts Receivable — Fees", "type" => "asset", "is_system" => false],
            ["code" => "1200", "name" => "Staff Loans & Advances Receivable", "type" => "asset", "is_system" => true],
            ["code" => "1300", "name" => "VAT Input (Recoverable)", "type" => "asset", "is_system" => true],
            ["code" => "2000", "name" => "Accounts Payable — Suppliers", "type" => "liability", "is_system" => true],
            ["code" => "2100", "name" => "VAT Output (Payable)", "type" => "liability", "is_system" => true],
            ["code" => "3000", "name" => "Opening Balance Equity", "type" => "equity", "is_system" => false],
            ["code" => "4000", "name" => "Fees Income", "type" => "income", "is_system" => true],
            ["code" => "4100", "name" => "Interest Income — Staff Loans", "type" => "income", "is_system" => true],
            ["code" => "4900", "name" => "Other Income", "type" => "income", "is_system" => false],
            ["code" => "5000", "name" => "Salaries & Wages Expense", "type" => "expense", "is_system" => false],
            ["code" => "5100", "name" => "Supplier Purchases & Expenses", "type" => "expense", "is_system" => true],
            ["code" => "5900", "name" => "Other Expense", "type" => "expense", "is_system" => false],
        ];
    }

    public static function seedDefaultChart(School $school): void
    {
        foreach (self::defaultChart() as $row) {
            self::firstOrCreate(
                ["school_id" => $school->id, "code" => $row["code"]],
                $row
            );
        }
    }

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * True if this is a normal-debit account (asset/expense — debits
     * increase it) vs normal-credit (liability/equity/income — credits
     * increase it). Used to sign the running balance correctly.
     */
    public function isDebitNormal(): bool
    {
        return in_array($this->type, ["asset", "expense"], true);
    }

    /**
     * Current balance, signed the way this account type is normally shown
     * (positive = a normal balance in the direction this account increases).
     */
    public function balance(): float
    {
        $totals = $this->lines()->selectRaw("COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c")->first();
        $debit = (float) $totals->d;
        $credit = (float) $totals->c;

        return $this->isDebitNormal() ? $debit - $credit : $credit - $debit;
    }
}
