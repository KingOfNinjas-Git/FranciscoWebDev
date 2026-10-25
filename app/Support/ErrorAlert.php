<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the site owner when something breaks on the live site, so a visitor does not have to report it.
 * At most one email per ten minutes (one broken page can fail many times), and nothing from the request is included
 * except the path: no form data, cookies or query strings.
 */
class ErrorAlert
{
    private const COOLDOWN_SECONDS = 600;

    public static function exception(\Throwable $e): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $path = '/' . ltrim((string) (request()?->path() ?? ''), '/');

        self::send(
            'Site error: ' . class_basename($e) . ' on ' . $path,
            [
                'Something went wrong on ' . config('app.url') . ' and the visitor saw an error page.',
                '',
                'Page: ' . $path,
                'Error: ' . class_basename($e) . ': ' . mb_substr($e->getMessage(), 0, 300),
                'Where: ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $e->getFile()) . ':' . $e->getLine(),
                'When: ' . now()->toDateTimeString() . ' (server time)',
                '',
                'The full details are in storage/logs/laravel.log on the server.',
                'You get at most one of these emails every 10 minutes.',
            ],
            'error-alert',
        );
    }

    /** @param string[] $lines */
    public static function send(string $subject, array $lines, string $key, int $cooldown = self::COOLDOWN_SECONDS): void
    {
        try {
            if (! Cache::add("alert-sent:$key", 1, $cooldown)) {
                return; // already told you recently
            }

            Mail::raw(implode("\n", $lines), function ($message) use ($subject) {
                $message->to(config('portfolio.contact_email'))->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Could not send an alert email: ' . $e->getMessage()); // never let the alert make things worse
        }
    }
}
