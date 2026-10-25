<?php

return [
    // Where contact-form briefs are delivered
    'contact_email' => env('CONTACT_EMAIL', 'franciscomfcavaco@gmail.com'),
    // Search Console "HTML tag" verification code (the content="..." value); leave empty until you have one
    'google_verification' => env('GOOGLE_SITE_VERIFICATION', '6fJd_383uWhiiszPpCgBYfeF5oOjucIknpK4xfmzbMA'),
    // IndexNow key: tells Bing and similar search engines about new pages (served at /<key>.txt). Not a secret.
    'indexnow_key' => env('INDEXNOW_KEY', '2e7f8fb023780aba71239bcadcd6157a'),
    'phone' => '+351 924 490 605',
    'linkedin' => 'https://www.linkedin.com/in/francisco-cavaco-dev',
    'github' => 'https://github.com/KingOfNinjas-Git',
];
