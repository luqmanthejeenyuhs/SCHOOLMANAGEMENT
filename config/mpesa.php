<?php

return [
    // "sandbox" or "production" — switch this and the base_url once you have live Daraja credentials
    "env" => env("MPESA_ENV", "sandbox"),

    "base_url" => env("MPESA_ENV", "sandbox") === "production"
        ? "https://api.safaricom.co.ke"
        : "https://sandbox.safaricom.co.ke",

    "consumer_key" => env("MPESA_CONSUMER_KEY"),
    "consumer_secret" => env("MPESA_CONSUMER_SECRET"),

    // Daraja sandbox default test shortcode/passkey — replace with your Paybill/Till + passkey from
    // the Daraja portal (https://developer.safaricom.co.ke) once you have them
    "shortcode" => env("MPESA_SHORTCODE", "174379"),
    "passkey" => env("MPESA_PASSKEY", "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"),

    "callback_url" => env("MPESA_CALLBACK_URL", env("APP_URL")."/mpesa/callback"),

    "transaction_type" => "CustomerPayBillOnline",
];
