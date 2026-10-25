<?php

return [
    // Secret key from https://console.anthropic.com. Lives only in .env, never in the code or the browser.
    'api_key' => env('ANTHROPIC_API_KEY'),

    'api_url' => 'https://api.anthropic.com/v1/messages',
    'api_version' => '2026-10-21',

    'model' => env('AI_MODEL', 'claude-opus-5-5'),
    // low | medium | high | xhigh | max. Higher thinks longer (slower, costs more).
    'effort' => env('AI_EFFORT', 'medium'),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 16000),
    // Seconds to wait for one API call
    'timeout' => (int) env('AI_TIMEOUT', 170),

    // Cost guard: maximum AI generations (drafts + translations) per day
    'daily_limit' => (int) env('AI_DAILY_LIMIT', 30),

    // Where the "site context" reader looks. Empty = APP_URL (the live site in production).
    'site_url' => env('AI_CONTEXT_SITE_URL'),
    // Upper bound for the context text sent with each draft request
    'context_chars' => 14000,
    'context_cache_seconds' => 300,
];
