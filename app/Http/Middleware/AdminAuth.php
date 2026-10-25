<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    // Log the admin out after this many idle minutes
    private const IDLE_MINUTES = 120;

    /**
     * Session-flag admin auth with an idle timeout.
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        $last = $session->get('admin_last_seen');

        if ($session->get('is_admin') && $last && now()->timestamp - $last > self::IDLE_MINUTES * 60) {
            $session->forget(['is_admin', 'admin_last_seen']);
        }

        if ($session->get('is_admin')) {
            $session->put('admin_last_seen', now()->timestamp);

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your admin session expired. Log in again.'], 401);
        }

        return redirect()->route('admin.login');
    }
}
