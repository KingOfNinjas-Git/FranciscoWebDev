<?php

return [
    // Content-Security-Policy. If a future change breaks something on the site, set SECURITY_CSP=false in .env
    // to switch only this header off while you investigate (the other security headers stay on).
    'csp' => (bool) env('SECURITY_CSP', true),

    // Send visitors on www.example.com to example.com (the address in APP_URL) with a permanent redirect
    'redirect_www' => (bool) env('REDIRECT_WWW', true),
];
