<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Is a live demo answering? Asks every address at the same time (never longer than four seconds) and remembers the
 * answer for ten minutes, so a busy page does not hammer the demos and a down demo never makes the page slow.
 */
class DemoStatus
{
    public const KEEP_SECONDS = 600;

    /**
     * @param  string[]  $urls
     * @return array<string, array{up: bool, ms: int|null, at: int}> keyed by address
     */
    public static function checkMany(array $urls): array
    {
        $urls = array_values(array_unique(array_filter($urls)));
        $found = [];
        $missing = [];

        foreach ($urls as $url) {
            $cached = Cache::get(self::key($url));
            if ($cached) {
                $found[$url] = $cached;
            } else {
                $missing[] = $url;
            }
        }

        if ($missing) {
            $started = microtime(true);
            try {
                $responses = Http::pool(fn ($pool) => array_map(
                    fn ($url) => $pool->as($url)->timeout(4)->connectTimeout(3)->withUserAgent('franciscowebdev-lab-status')->get($url),
                    $missing,
                ));
            } catch (\Throwable $e) {
                $responses = [];
            }

            foreach ($missing as $url) {
                $response = $responses[$url] ?? null;
                $up = $response instanceof \Illuminate\Http\Client\Response && $response->status() < 400;
                $found[$url] = ['up' => $up, 'ms' => $up ? (int) round((microtime(true) - $started) * 1000) : null, 'at' => now()->timestamp];
                Cache::put(self::key($url), $found[$url], self::KEEP_SECONDS);
            }
        }

        return $found;
    }

    private static function key(string $url): string
    {
        return 'demo-status:' . md5($url);
    }
}
