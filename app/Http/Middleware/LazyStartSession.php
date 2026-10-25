<?php

namespace App\Http\Middleware;

use App\Support\FormToken;
use Closure;
use Illuminate\Session\Middleware\StartSession;

/**
 * Starts a session only when one is needed: the admin area, sending the contact form, or a visitor who
 * already has a session cookie (for example a logged-in admin browsing the public pages).
 *
 * Everyone else (readers, search engines, link-preview bots) gets no session row in the database and no cookies.
 */
class LazyStartSession extends StartSession
{
    public function handle($request, Closure $next)
    {
        if (! $this->needsSession($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    private function needsSession($request): bool
    {
        return $request->hasCookie(config('session.cookie'))
            || $request->is('admin', 'admin/*')
            || $this->isGenuineContactSubmission($request);
    }

    // The contact form needs a session only to show its result after the redirect; bots without a valid token get none
    private function isGenuineContactSubmission($request): bool
    {
        return $request->isMethod('POST')
            && $request->is('contact')
            && ! $request->filled('website')
            && in_array(FormToken::check($request->input('_ft')), ['ok', 'expired'], true);
    }
}
