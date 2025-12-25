<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        // Check for a cookie first
        if ($request->cookies->has('site_locale')) {
            $locale = $request->cookie('site_locale');
        }

        // Fallback to session if present
        if (!$locale && $request->session()->has('site_locale')) {
            $locale = $request->session()->get('site_locale');
        }

        // Validate locale
        if (in_array($locale, ['en', 'pt'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
