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
        App::setLocale($this->resolve($request));

        return $next($request);
    }

    // The URL decides (/pt, /es, / = English); other pages (contact POST, admin) use the saved choice
    private function resolve(Request $request): string
    {
        $first = $request->segment(1);
        if (in_array($first, ['pt', 'es'], true)) {
            return $first;
        }

        if ($request->path() === '/') {
            return 'en';
        }

        $cookie = $request->cookie('site_locale');

        return in_array($cookie, self::LOCALES, true) ? $cookie : config('app.locale');
    }

    public static function homeUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('home') : route('home.' . $locale);
    }
}
