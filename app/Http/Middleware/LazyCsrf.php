<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\TokenMismatchException;

/**
 * CSRF protection for requests that have a session. A request that changes data without any session is refused
 * (the only public form, the contact form, uses its own signed token instead and is excluded in bootstrap/app.php).
 */
class LazyCsrf extends ValidateCsrfToken
{
    public function handle($request, Closure $next)
    {
        if (! $request->hasSession()) {
            if ($this->isReading($request) || $this->inExceptArray($request)) {
                return $next($request);
            }

            throw new TokenMismatchException('CSRF token mismatch.');
        }

        return parent::handle($request, $next);
    }
}
