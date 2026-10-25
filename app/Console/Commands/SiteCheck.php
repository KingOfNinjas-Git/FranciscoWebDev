<?php

namespace App\Console\Commands;

use App\Support\ErrorAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SiteCheck extends Command
{
    protected $signature = 'site:check {--url= : Site address to test (default: APP_URL)} {--quiet-ok : Print nothing when everything is fine}';

    protected $description = 'Loads the main pages of the live site and emails you if one is broken or the HTTPS certificate is about to expire';

    /** path => text that must be on the page */
    private const PAGES = [
        '/' => 'Francisco Cavaco',
        '/pt' => 'Francisco Cavaco',
        '/es' => 'Francisco Cavaco',
        '/blog' => 'Blog',
        '/sitemap.xml' => '<urlset',
        '/health' => '"ok":true',
    ];

    public function handle(): int
    {
        $base = rtrim($this->option('url') ?: config('app.url'), '/');
        $problems = [];

        foreach (self::PAGES as $path => $needle) {
            try {
                $response = Http::timeout(20)->withHeaders(['User-Agent' => 'site-check'])->get($base . $path);
                if ($response->status() !== 200) {
                    $problems[] = "$path answered HTTP {$response->status()}";
                } elseif (! str_contains($response->body(), $needle)) {
                    $problems[] = "$path loaded but does not look right (expected text missing)";
                }
            } catch (\Throwable $e) {
                $problems[] = "$path could not be reached (" . class_basename($e) . ')';
            }
        }

        if (($days = $this->certificateDaysLeft($base)) !== null && $days < 14) {
            $problems[] = "the HTTPS certificate expires in $days day(s)";
        }

        if ($problems === []) {
            if (! $this->option('quiet-ok')) {
                $this->info('All ' . count(self::PAGES) . ' pages are fine.');
            }

            return self::SUCCESS;
        }

        foreach ($problems as $problem) {
            $this->error($problem);
        }

        ErrorAlert::send(
            'Site check: ' . count($problems) . ' problem(s) on ' . parse_url($base, PHP_URL_HOST),
            array_merge(
                ['The automatic check of ' . $base . ' found:', ''],
                array_map(fn ($p) => '- ' . $p, $problems),
                ['', 'Checked at ' . now()->toDateTimeString() . ' (server time). You get at most one of these emails per hour.'],
            ),
            'site-check',
            3600,
        );

        return self::FAILURE;
    }

    private function certificateDaysLeft(string $base): ?int
    {
        $host = parse_url($base, PHP_URL_HOST);
        if (! $host || parse_url($base, PHP_URL_SCHEME) !== 'https') {
            return null;
        }

        try {
            $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'SNI_enabled' => true, 'peer_name' => $host]]);
            $socket = @stream_socket_client("ssl://$host:443", $errno, $error, 10, STREAM_CLIENT_CONNECT, $context);
            if (! $socket) {
                return null;
            }
            $cert = openssl_x509_parse(stream_context_get_params($socket)['options']['ssl']['peer_certificate']);

            return isset($cert['validTo_time_t']) ? (int) floor(($cert['validTo_time_t'] - time()) / 86400) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
