<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public const LOCALES = ['en', 'pt', 'es'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('site_locale');

        if (in_array($locale, self::LOCALES, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
