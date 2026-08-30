<?php

return [
    // Standard Kenya VAT rate, per the VAT Act (Cap 476) — currently 16%.
    // Applied here to supplier purchases (input VAT, reclaimable).
    //
    // IMPORTANT: school fees / core educational services are VAT-EXEMPT
    // under the First Schedule of the VAT Act — do NOT apply VAT to
    // FeeType/FeeInvoice records. This config intentionally is not wired
    // into anything on the fees side.
    'standard_rate' => env('VAT_STANDARD_RATE', 16),
];
