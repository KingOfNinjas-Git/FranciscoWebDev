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

    // The URL decides (/pt, /es, / and /blog = English); other pages (contact POST, admin) use the saved choice
    private function resolve(Request $request): string
    {
        $first = $request->segment(1);
        if (in_array($first, ['pt', 'es'], true)) {
            return $first;
        }

        if ($request->path() === '/' || in_array($first, ['blog', 'privacy', 'projects', 'accessibility', 'lab'], true)) {
            return 'en';
        }

        // The contact form sends the language of the page it was on
        if ($request->isMethod('POST') && $request->path() === 'contact' && in_array($request->input('_locale'), self::LOCALES, true)) {
            return $request->input('_locale');
        }

        $cookie = $request->cookie('site_locale');

        return in_array($cookie, self::LOCALES, true) ? $cookie : config('app.locale');
    }

    public static function homeUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('home') : route('home.' . $locale);
    }

    public static function blogUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('blog.index') : route("blog.$locale.index");
    }

    public static function privacyUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('privacy') : route("privacy.$locale");
    }

    public static function projectUrl(string $slug, ?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('projects.show', $slug) : route("projects.$locale.show", $slug);
    }

    public static function labUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('lab') : route("lab.$locale");
    }

    public static function accessibilityUrl(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('accessibility') : route("accessibility.$locale");
    }

    public static function postUrl(string $slug, ?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === 'en' ? route('blog.show', $slug) : route("blog.$locale.show", $slug);
    }
}
