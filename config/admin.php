<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap admin account
    |--------------------------------------------------------------------------
    |
    | When migrations run, the user with this email is promoted to admin.
    | You can also run: php artisan admin:promote {username-or-email}
    |
    */

    'bootstrap_email' => env('ADMIN_EMAIL'),

];
