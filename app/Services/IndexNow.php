<?php

namespace App\Services;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells search engines that support the IndexNow protocol (Bing, and through it DuckDuckGo and others; Yandex, Seznam)
 * that a page is new or changed, so they fetch it within minutes instead of days. Google does not take part in
 * IndexNow: it finds changes through the sitemap (or "Request indexing" in Search Console).
 *
 * The key proves the site is ours: the same text is served at /<key>.txt (see routes/web.php).
 */
class IndexNow
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    public static function enabled(): bool
    {
        return app()->environment('production') && filled(config('portfolio.indexnow_key'));
    }

    /** The post's page in each language. */
    public static function postUrls(Post $post): array
    {
        return array_map(fn ($locale) => SetLocale::postUrl($post->slug, $locale), SetLocale::LOCALES);
    }

    public static function projectUrls(string $slug): array
    {
        return array_map(fn ($locale) => SetLocale::projectUrl($slug, $locale), SetLocale::LOCALES);
    }

    /** Sends the addresses. Never throws: a failed ping must not affect saving. */
    public static function ping(array $urls): bool
    {
        if (! self::enabled() || $urls === []) {
            return false;
        }

        $key = config('portfolio.indexnow_key');
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        try {
            $response = Http::timeout(8)->asJson()->post(self::ENDPOINT, [
                'host' => $host,
                'key' => $key,
                'keyLocation' => 'https://' . $host . '/' . $key . '.txt',
                'urlList' => array_values($urls),
            ]);

            if (! $response->successful()) {
                Log::info('IndexNow answered HTTP ' . $response->status());
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::info('IndexNow ping failed: ' . $e->getMessage());

            return false;
        }
    }

    /** Pings after the response is sent, so saving is never slowed down. */
    public static function pingLater(array $urls): void
    {
        if (self::enabled() && $urls !== []) {
            dispatch(fn () => self::ping($urls))->afterResponse();
        }
    }
}
