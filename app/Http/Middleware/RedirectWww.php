<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * www.example.com and example.com are two different addresses to search engines.
 * Permanently send "www." visitors to the address configured in APP_URL.
 */
class RedirectWww
{
    public function handle(Request $request, Closure $next)
    {
        if (config('security.redirect_www')) {
            $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            // Only the exact "www." + our own host is redirected, so this can never point visitors elsewhere
            if ($canonicalHost && strtolower($request->getHost()) === 'www.' . strtolower($canonicalHost)) {
                return redirect()->to(rtrim(config('app.url'), '/') . $request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
