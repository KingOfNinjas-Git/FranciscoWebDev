<?php

return [
    'password' => env('ADMIN_PASSWORD'),

    // Emergency switch: if you lose your phone AND your recovery codes, set ADMIN_2FA_DISABLED=true in .env
    // to log in with the password alone, then set two-factor up again from the Security page.
    'two_factor_disabled' => (bool) env('ADMIN_2FA_DISABLED', false),
];
