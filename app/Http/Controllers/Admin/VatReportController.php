<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierBill;
use Illuminate\Http\Request;

class VatReportController extends Controller
{
    /**
     * Input VAT only — VAT the school paid to suppliers and can reclaim.
     * There is deliberately no output VAT here: school fees are VAT-exempt
     * educational services under Kenya's VAT Act, so nothing on the fees
     * side charges VAT to reconcile against.
     */
    public function index(Request $request)
    {
        $from = $request->get("from", now()->startOfMonth()->toDateString());
        $to = $request->get("to", now()->toDateString());

        $bills = SupplierBill::with("supplier")
            ->where("vat_amount", ">", 0)
            ->whereBetween("bill_date", [$from, $to])
            ->orderBy("bill_date")
            ->get();

        $totalNet = $bills->sum("amount");
        $totalVat = $bills->sum("vat_amount");
        $totalGross = $bills->sum("total_amount");

        return view("admin.finance.vat_report", compact("bills", "from", "to", "totalNet", "totalVat", "totalGross"));
    }
}
