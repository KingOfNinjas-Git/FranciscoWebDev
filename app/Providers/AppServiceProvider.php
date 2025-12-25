<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set application locale from cookie or session (if available)
        try {
            $request = request();
            $locale = null;
            if ($request && $request->cookies->has('site_locale')) {
                $locale = $request->cookie('site_locale');
            }
            if (!$locale && $request && $request->session()->has('site_locale')) {
                $locale = $request->session()->get('site_locale');
            }
            if (in_array($locale, ['en', 'pt'])) {
                App::setLocale($locale);
            }
        } catch (\Throwable $e) {
            Log::debug('Locale check failed: ' . $e->getMessage());
        }
    }
}
