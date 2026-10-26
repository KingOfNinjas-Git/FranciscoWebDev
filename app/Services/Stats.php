<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Anonymous visit counts: one number per day per page, per referring site and per button. Nothing that identifies a
 * visitor is read or stored (no cookie, no IP address, no user agent), so no consent banner is needed.
 */
class Stats
{
    /** Buttons and links that may be counted (the page sends the name; anything else is ignored). */
    public const EVENTS = [
        'lead' => 'Briefs sent',
        'hero_projects' => 'Hero: View projects',
        'hero_contact' => 'Hero: Start a project',
        'service_ask' => 'Services: Ask about this',
        'project_open' => 'Project card opened',
        'project_live' => 'Project: Visit live site',
        'project_repo' => 'Project: View source code',
        'project_cta' => 'Project: Start a project',
        'blog_share' => 'Blog: share or copy link',
        'whatsapp' => 'Contact: WhatsApp button',
        'phone' => 'Contact: phone link',
        'email' => 'Contact: email link',
    ];

    private const RETENTION_DAYS = 400;

    /** Words in a browser's identification that mean "robot, monitor or tool", not a person. */
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|monitor|uptime|lighthouse|pagespeed|headless|phantom|curl|wget|python|java\/|http-?client|libwww|axios|node-fetch|go-http|facebookexternalhit|embedly|whatsapp|telegram|discord|site-check|pingdom|gtmetrix/i';

    public static function looksLikeBot(?string $userAgent): bool
    {
        return $userAgent === null || $userAgent === '' || preg_match(self::BOT_PATTERN, $userAgent) === 1;
    }

    /** Adds one to today's count. Never throws: statistics must not be able to break a page. */
    public static function hit(string $kind, string $name): void
    {
        $name = mb_substr($name, 0, 190);
        $day = now()->toDateString();
        $where = ['day' => $day, 'kind' => $kind, 'name' => $name];

        try {
            if (DB::table('stats')->where($where)->increment('total') === 0) {
                try {
                    DB::table('stats')->insert($where + ['total' => 1]);
                } catch (QueryException $e) {
                    DB::table('stats')->where($where)->increment('total'); // another visitor created the row a moment ago
                }
            }

            if (random_int(1, 300) === 1) {
                DB::table('stats')->where('day', '<', now()->subDays(self::RETENTION_DAYS)->toDateString())->delete();
            }
        } catch (\Throwable $e) {
            // table missing (before the migration) or database busy: skip silently
        }
    }

    /** Counts for the last $days days (today included). */
    public static function report(int $days): array
    {
        $from = now()->subDays($days - 1)->toDateString();
        $rows = DB::table('stats')->where('day', '>=', $from)->get(['day', 'kind', 'name', 'total']);

        $pages = $rows->where('kind', 'page');
        $perDay = $pages->groupBy('day')->map(fn (Collection $g) => (int) $g->sum('total'));
        $series = collect(range($days - 1, 0))->map(fn ($ago) => [
            'day' => now()->subDays($ago)->toDateString(),
            'views' => (int) ($perDay[now()->subDays($ago)->toDateString()] ?? 0),
        ]);

        $top = fn (Collection $c, int $limit) => $c->groupBy('name')->map(fn (Collection $g) => (int) $g->sum('total'))->sortDesc()->take($limit);

        $events = $rows->where('kind', 'event')->groupBy('name')->map(fn (Collection $g) => (int) $g->sum('total'));
        $views = (int) $pages->sum('total');

        return [
            'views' => $views,
            'series' => $series,
            'pages' => $top($pages, 15),
            'projects' => $top($pages->filter(fn ($r) => preg_match('~^(/(pt|es))?/projects/~', $r->name)), 8),
            'posts' => $top($pages->filter(fn ($r) => preg_match('~^(/(pt|es))?/blog/[^/]+~', $r->name)), 8),
            'referrers' => $top($rows->where('kind', 'ref'), 10),
            'languages' => collect(['en' => 0, 'pt' => 0, 'es' => 0])->map(fn ($_, $code) => (int) $pages->filter(fn ($r) => $code === 'en' ? ! preg_match('~^/(pt|es)(/|$)~', $r->name) : str_starts_with($r->name, "/$code"))->sum('total')),
            'events' => $events,
            'leads' => (int) ($events['lead'] ?? 0),
        ];
    }
}
