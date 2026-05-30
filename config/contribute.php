<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contribute Form Recipient
    |--------------------------------------------------------------------------
    |
    | Email address that receives messages from the /contribute contact form.
    |
    */

    'mail_to' => env('CONTRIBUTE_MAIL_TO', 'cdcoppercoins@gmail.com'),

    /*
    |--------------------------------------------------------------------------
    | Contribute Form Sender (appears in your inbox)
    |--------------------------------------------------------------------------
    */

    'mail_from_name' => env('CONTRIBUTE_MAIL_FROM_NAME', 'mlp question'),

    'mail_from_address' => env('CONTRIBUTE_MAIL_FROM_ADDRESS', 'cdcoppercoins@gmail.com'),

];
