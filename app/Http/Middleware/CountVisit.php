<?php

namespace App\Http\Middleware;

use App\Services\Stats;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Counts a page view (anonymously, see App\Services\Stats) after the page has been sent, so it never slows a visit down.
 * Only real pages are counted: not robots, not the admin, not visitors who send "Do Not Track", not feeds or images.
 */
class CountVisit
{
    /** Routes that are public pages (route names; "*" matches the rest of the name). */
    private const PAGES = ['home', 'home.*', 'blog.index', 'blog.*.index', 'blog.show', 'blog.*.show', 'projects.show', 'projects.*.show', 'privacy', 'privacy.*'];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return;
        }
        if (! $request->route() || ! $request->routeIs(...self::PAGES)) {
            return;
        }
        if ($request->header('DNT') === '1' || $request->header('Sec-GPC') === '1' || Stats::looksLikeBot($request->userAgent())) {
            return;
        }
        // The admin looking at their own site is not a visit
        if ($request->hasSession() && $request->session()->get('is_admin')) {
            return;
        }
        // Blog drafts are previews, not visits
        if (str_contains((string) $response->headers->get('X-Robots-Tag'), 'noindex')) {
            return;
        }

        Stats::hit('page', '/' . ltrim($request->path(), '/'));

        $referrer = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        $own = $request->getHost();
        $referrer = $referrer ? preg_replace('/^www\./', '', mb_strtolower($referrer)) : null;

        if ($referrer === null) {
            Stats::hit('ref', 'direct');
        } elseif ($referrer !== preg_replace('/^www\./', '', $own)) {
            Stats::hit('ref', $referrer);
        }
    }
}
