<?php

return [
    "mailgun" => [
        "domain" => env("MAILGUN_DOMAIN"),
        "secret" => env("MAILGUN_SECRET"),
    ],

    "africastalking" => [
        // "sandbox" hits Africa's Talking test environment (no real SMS billed/sent, but the
        // request/response cycle behaves identically). Set to "production" once you have a
        // paid username + live API key from https://africastalking.com
        "env" => env("AT_ENV", "sandbox"),
        "username" => env("AT_USERNAME", "sandbox"),
        "api_key" => env("AT_API_KEY"),
        "sender_id" => env("AT_SENDER_ID"), // optional shortcode/alphanumeric sender ID
    ],
];
