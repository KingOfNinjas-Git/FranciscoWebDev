<?php

namespace App\Support;

/**
 * A signed, time-stamped token for the public contact form. It proves the form was loaded from this site a
 * little while ago, without needing a session (so readers never receive cookies).
 */
class FormToken
{
    private const MIN_AGE = 2;       // seconds: nobody fills in this form faster; bots do
    private const MAX_AGE = 7200;    // 2 hours

    public static function make(): string
    {
        $time = time();

        return $time . '.' . self::sign($time);
    }

    /**
     * @return string 'ok' | 'too_fast' | 'expired' | 'invalid'
     */
    public static function check(?string $token): string
    {
        if (! is_string($token) || ! preg_match('/^(\d{9,12})\.([a-f0-9]{64})$/', $token, $m)) {
            return 'invalid';
        }

        if (! hash_equals(self::sign((int) $m[1]), $m[2])) {
            return 'invalid';
        }

        $age = time() - (int) $m[1];

        return match (true) {
            $age < self::MIN_AGE => 'too_fast',
            $age > self::MAX_AGE => 'expired',
            default => 'ok',
        };
    }

    private static function sign(int $time): string
    {
        return hash_hmac('sha256', 'contact-form|' . $time, (string) config('app.key'));
    }
}
