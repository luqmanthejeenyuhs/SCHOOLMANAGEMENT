<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = Receipt::with(["payment.invoice.student.user", "payment.invoice.feeType", "issuedBy"])
            ->latest()
            ->paginate(15);

        return view("admin.receipts.index", compact("receipts"));
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(["payment.invoice.student.user", "payment.invoice.feeType", "issuedBy"]);

        return view("admin.receipts.show", compact("receipt"));
    }
}
