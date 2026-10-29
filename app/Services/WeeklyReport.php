<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The Monday email: how the last 7 days went (visits, where they came from, what was clicked), which briefs are
 * waiting for an answer, and what was published. Plain text, written to be read in 30 seconds.
 */
class WeeklyReport
{
    /** @return array{subject: string, lines: string[]} */
    public static function build(): array
    {
        $from = now()->subDays(6);
        $lines = ['Your week on ' . parse_url(config('app.url'), PHP_URL_HOST) . ' (' . $from->format('j M') . ' to ' . now()->format('j M') . ')', ''];

        $views = null;
        try {
            $week = Stats::report(7);
            $two = Stats::report(14);
            $views = $week['views'];
            $before = $two['views'] - $week['views'];

            $change = match (true) {
                $before === 0 && $views === 0 => 'no visits yet',
                $before === 0 => 'the week before had none',
                default => (($views >= $before ? '+' : '') . round(($views - $before) / $before * 100) . '% compared with the week before (' . $before . ')'),
            };
            $lines[] = "VISITS: $views ($change)";

            $page = fn (string $path) => $path === '/' ? 'Home (English)' : (in_array($path, ['/pt', '/es'], true) ? 'Home (' . strtoupper(ltrim($path, '/')) . ')' : $path);
            if ($week['pages']->isNotEmpty()) {
                $lines[] = '';
                $lines[] = 'Most read pages:';
                foreach ($week['pages']->take(5) as $path => $total) {
                    $lines[] = "  $total  " . $page($path);
                }
            }
            if ($week['referrers']->isNotEmpty()) {
                $lines[] = '';
                $lines[] = 'Where visitors came from:';
                foreach ($week['referrers']->take(5) as $site => $total) {
                    $lines[] = "  $total  $site";
                }
            }

            $clicks = collect(Stats::EVENTS)->filter(fn ($label, $key) => ($week['events'][$key] ?? 0) > 0);
            if ($clicks->isNotEmpty()) {
                $lines[] = '';
                $lines[] = 'Clicks and actions:';
                foreach ($clicks as $key => $label) {
                    $lines[] = '  ' . $week['events'][$key] . '  ' . $label;
                }
            }
        } catch (\Throwable $e) {
            $lines[] = 'VISITS: the statistics are not available yet.';
        }

        // Briefs
        $lines[] = '';
        try {
            $new = Lead::where('created_at', '>=', $from->copy()->startOfDay())->count();
            $waiting = Lead::where('status', 'new')->orderBy('created_at')->get();
            $lines[] = "BRIEFS: $new new this week";
            if ($waiting->isNotEmpty()) {
                $lines[] = 'Waiting for your answer:';
                foreach ($waiting as $lead) {
                    $lines[] = '  - ' . $lead->name . ', ' . $lead->created_at->diffForHumans() . '  ' . route('admin.leads.show', $lead->id);
                }
            } else {
                $lines[] = 'Nobody is waiting for an answer.';
            }
        } catch (\Throwable $e) {
            $lines[] = 'BRIEFS: not available yet.';
            $new = 0;
        }

        // Posts
        try {
            $posts = Post::published()->where('published_at', '>=', $from->copy()->startOfDay())->get();
            $lines[] = '';
            if ($posts->isNotEmpty()) {
                $lines[] = 'PUBLISHED THIS WEEK:';
                foreach ($posts as $post) {
                    $lines[] = '  - ' . $post->title;
                }
            } else {
                $lines[] = 'PUBLISHED THIS WEEK: nothing. A new post every week or two keeps search engines coming back.';
            }
        } catch (\Throwable $e) {
            // the blog tables may not exist yet
        }

        array_push($lines, ...self::safetyCopies());

        $lines[] = '';
        $lines[] = 'THIS WEEK, TWO MINUTES:';
        $lines[] = '  1. Search Console -> Performance: which searches showed you? https://search.google.com/search-console';
        $lines[] = '  2. After publishing a post, paste its address into Search Console and press "Request indexing".';
        $lines[] = '';
        $lines[] = 'Everything in detail: ' . route('admin.stats');

        return [
            'subject' => 'Your week: ' . ($views ?? 0) . ' visits, ' . ($new ?? 0) . ' new brief(s)',
            'lines' => $lines,
        ];
    }

    /**
     * Where the safety copies are: the nightly database copy on the server, and the last time the copy to your own computer
     * ran (scripts/pull-backups.sh leaves a note with the time in storage/app/backups/.last-offsite-pull).
     *
     * @return string[]
     */
    public static function safetyCopies(): array
    {
        $dir = storage_path('app/backups');
        $lines = ['', 'SAFETY COPIES:'];

        $times = array_map('filemtime', glob($dir . '/database-*.sql.gz') ?: []);
        $latest = $times ? max($times) : null;
        $lines[] = $latest
            ? '  On the server: the last database copy was made ' . Carbon::createFromTimestamp($latest)->diffForHumans() . '.'
            : '  On the server: no database copy yet (one is made every night at 03:15).';

        $note = $dir . '/.last-offsite-pull';
        $pulled = is_file($note) ? (int) trim((string) file_get_contents($note)) : 0;
        if ($pulled > now()->subDays(10)->timestamp) {
            $lines[] = '  On your computer: copied ' . Carbon::createFromTimestamp($pulled)->diffForHumans() . '.';
        } else {
            $lines[] = '  On your computer: ' . ($pulled ? 'the last copy was ' . Carbon::createFromTimestamp($pulled)->diffForHumans() : 'no copy yet')
                . '. Run scripts/pull-backups.sh in Git Bash so a copy also lives outside Hostinger.';
        }

        return $lines;
    }

    public static function send(): bool
    {
        $report = self::build();

        try {
            Mail::raw(implode("\n", $report['lines']), fn ($m) => $m->to(config('portfolio.contact_email'))->subject($report['subject']));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Could not send the weekly report: ' . $e->getMessage());

            return false;
        }
    }
}
