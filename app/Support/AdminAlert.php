<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Security emails to the site owner: someone signed in, the password was changed, or wrong passwords keep coming.
 * They contain the time, the IP address and the browser of the request, and nothing secret.
 */
class AdminAlert
{
    public static function signedIn(Request $request): void
    {
        self::send('Admin sign-in on ' . self::host(), array_merge([
            'Someone signed in to the admin of ' . self::host() . '.',
            '',
        ], self::details($request), [
            '',
            'If this was you, there is nothing to do.',
            'If it was not: sign in, open Security, change the password and press "Sign out everywhere".',
        ]));
    }

    public static function passwordChanged(Request $request): void
    {
        self::send('Admin password changed on ' . self::host(), array_merge([
            'The admin password of ' . self::host() . ' was changed, and every other signed-in browser was signed out.',
            '',
        ], self::details($request), [
            '',
            'If this was not you, use the rescue command on the server (see docs/OPERATIONS.md) and change the password again.',
        ]));
    }

    public static function signedOutEverywhere(Request $request): void
    {
        self::send('Admin signed out everywhere on ' . self::host(), array_merge([
            'Every signed-in admin browser of ' . self::host() . ' was signed out.',
            '',
        ], self::details($request)));
    }

    /** Counts wrong passwords from anyone; from the third in ten minutes you get one email per hour. */
    public static function wrongPassword(Request $request): void
    {
        try {
            $key = 'admin-wrong-passwords';
            Cache::add($key, 0, 600);
            $count = Cache::increment($key);

            if ($count >= 3 && Cache::add('admin-wrong-passwords-alert', 1, 3600)) {
                self::send('Wrong admin passwords on ' . self::host(), array_merge([
                    "$count wrong passwords were entered on the admin login in the last 10 minutes.",
                    '',
                ], self::details($request), [
                    '',
                    'The login blocks fast guessing (5 tries a minute) and needs your phone code too, so nothing got in.',
                    'You get at most one of these emails per hour.',
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('Could not count wrong admin passwords: ' . $e->getMessage());
        }
    }

    /** @return string[] */
    private static function details(Request $request): array
    {
        return [
            'When: ' . now()->toDateTimeString() . ' (' . config('app.timezone') . ' server time)',
            'IP address: ' . $request->ip(),
            'Browser: ' . mb_substr((string) $request->userAgent(), 0, 160),
        ];
    }

    private static function host(): string
    {
        return (string) parse_url(config('app.url'), PHP_URL_HOST);
    }

    private static function send(string $subject, array $lines): void
    {
        try {
            Mail::raw(implode("\n", $lines), fn ($m) => $m->to(config('portfolio.contact_email'))->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('Could not send a security email: ' . $e->getMessage()); // never block a login because an email failed
        }
    }
}
