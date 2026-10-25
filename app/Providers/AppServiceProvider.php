<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The blog's AI drafting talks to whichever provider config/ai.php selects
        $this->app->bind(\App\Services\Ai\LlmClient::class, fn () => config('ai.provider') === 'gemini'
            ? new \App\Services\Ai\GeminiClient()
            : new \App\Services\Ai\ClaudeClient());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
