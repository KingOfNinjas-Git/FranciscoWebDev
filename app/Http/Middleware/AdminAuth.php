<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Simple admin auth middleware: checks session flag.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('is_admin')) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
