<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Like ShareErrorsFromSession, but copes with requests that have no session (an empty error bag is shared).
 */
class LazyShareErrors extends ShareErrorsFromSession
{
    public function handle($request, Closure $next)
    {
        if (! $request->hasSession()) {
            $this->view->share('errors', new ViewErrorBag);

            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
