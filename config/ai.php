<?php

// Which AI writes the drafts: "gemini" (Google) or "anthropic" (Claude).
// Set AI_PROVIDER explicitly, or just add one key: GEMINI_API_KEY picks Gemini, ANTHROPIC_API_KEY picks Claude.
$provider = env('AI_PROVIDER') ?: (env('GEMINI_API_KEY') ? 'gemini' : 'anthropic');
$gemini = $provider === 'gemini';

return [
    'provider' => $provider,

    // The key of the active provider. It lives only in .env, never in the code or the browser.
    'api_key' => $gemini ? env('GEMINI_API_KEY') : env('ANTHROPIC_API_KEY'),
    'key_name' => $gemini ? 'GEMINI_API_KEY' : 'ANTHROPIC_API_KEY',

    'model' => $gemini ? env('AI_GEMINI_MODEL', 'gemini-3.5-flash-lite') : env('AI_MODEL', 'claude-opus-5-5'),

    // Gemini only: how long the model thinks before answering (low | medium | high). Lower = much faster drafts.
    // Leave AI_GEMINI_THINKING empty in .env to use Google's default.
    'gemini_thinking' => env('AI_GEMINI_THINKING', 'low'),

    // Claude only
    'api_url' => 'https://api.anthropic.com/v1/messages',
    'api_version' => '2026-10-21',
    // low | medium | high | xhigh | max. Higher thinks longer (slower, costs more).
    'effort' => env('AI_EFFORT', 'medium'),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 16000),
    // Seconds to wait for one API call
    'timeout' => (int) env('AI_TIMEOUT', 120),

    // Cost guard: maximum AI generations (drafts + translations) per day
    'daily_limit' => (int) env('AI_DAILY_LIMIT', 30),

    // Where the "site context" reader looks. Empty = APP_URL (the live site in production).
    'site_url' => env('AI_CONTEXT_SITE_URL'),
    // Upper bound for the context text sent with each draft request
    'context_chars' => 14000,
    'context_cache_seconds' => 300,
];
