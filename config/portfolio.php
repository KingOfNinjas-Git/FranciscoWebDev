<?php

return [
    // Where contact-form briefs are delivered
    'contact_email' => env('CONTACT_EMAIL', 'franciscomfcavaco@gmail.com'),
    // Search Console "HTML tag" verification code (the content="..." value); leave empty until you have one
    'google_verification' => env('GOOGLE_SITE_VERIFICATION', '6fJd_383uWhiiszPpCgBYfeF5oOjucIknpK4xfmzbMA'),
    // IndexNow key: tells Bing and similar search engines about new pages (served at /<key>.txt). Not a secret.
    'indexnow_key' => env('INDEXNOW_KEY', '2e7f8fb023780aba71239bcadcd6157a'),
    // Dates for the sitemap's <lastmod>. Search engines learn to ignore dates that change on every visit, so these
    // only change when you change the page: raise site_updated when you redesign or rewrite the home page, and
    // privacy_updated when you change the privacy notice. Posts and projects use their own saved dates.
    'site_updated' => '2026-10-26',
    'privacy_updated' => '2026-10-26',
    'phone' => '+351 924 490 605',
    'linkedin' => 'https://www.linkedin.com/in/francisco-cavaco-dev',
    'github' => 'https://github.com/KingOfNinjas-Git',
];
