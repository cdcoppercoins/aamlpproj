<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Member newsletter delivery
    |--------------------------------------------------------------------------
    |
    | Emails are sent in small batches from the admin "sending" screen so shared
    | hosting does not need a long-running queue worker.
    |
    */
    'batch_size' => (int) env('NEWSLETTER_BATCH_SIZE', 15),

    'from_address' => env('NEWSLETTER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'cdcoppercoins@gmail.com')),

    'from_name' => env('NEWSLETTER_FROM_NAME', env('MAIL_FROM_NAME', 'MiniLicensePlates.com')),
];
