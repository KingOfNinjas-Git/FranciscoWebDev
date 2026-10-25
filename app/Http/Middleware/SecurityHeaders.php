<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds browser security headers to every response.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // A fresh nonce per request: the few inline scripts carry it, and Vite adds it to its own script tags
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Browsers ignore HSTS over plain HTTP, so it is safe to send whenever the site is meant to be HTTPS
        if (app()->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        // Not while the Vite dev server runs (public/hot): it serves scripts from another origin
        if (config('security.csp') && !file_exists(public_path('hot'))) {
            $headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'unsafe-inline'",   // a few inline style="" attributes (animation delays)
                "img-src 'self' data: https:",         // blog posts may embed external images
                "font-src 'self'",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]));
        }

        return $response;
    }
}
