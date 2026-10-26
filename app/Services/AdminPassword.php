<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * The admin password. Until it is changed from the Security page it is the ADMIN_PASSWORD line in the server's .env;
 * after that it lives in the database as a hash (nobody, including whoever can read the database, can read it back).
 *
 * "Epoch" is a counter that every signed-in browser remembers. Changing the password or pressing "Sign out everywhere"
 * raises it, and any browser still holding the old number is signed out on its next click.
 */
class AdminPassword
{
    private const HASH = 'password_hash';
    private const EPOCH = 'session_epoch';
    private const LOGINS = 'recent_logins';

    public const MIN_LENGTH = 12;

    public function verify(string $input): bool
    {
        $stored = $this->get(self::HASH);
        if ($stored !== null) {
            return Hash::check($input, $stored);
        }

        $env = (string) config('admin.password');

        return $env !== '' && hash_equals($env, $input);
    }

    /** Is the password kept as a hash in the database (rather than in .env)? */
    public function stored(): bool
    {
        return $this->get(self::HASH) !== null;
    }

    public function change(string $new): void
    {
        $this->put(self::HASH, Hash::make($new));
        $this->bumpEpoch();
    }

    /** Back to the .env password (the rescue command uses this). */
    public function resetToEnv(): void
    {
        $this->forget(self::HASH);
        $this->bumpEpoch();
    }

    public function epoch(): int
    {
        return (int) $this->get(self::EPOCH);
    }

    public function bumpEpoch(): int
    {
        $next = $this->epoch() + 1;
        $this->put(self::EPOCH, (string) $next);

        return $next;
    }

    /** Remembers a sign-in (newest first, last 10) for the Security page. */
    public function recordLogin(string $ip, string $browser): void
    {
        $list = $this->recentLogins();
        array_unshift($list, ['at' => now()->toDateTimeString(), 'ip' => $ip, 'browser' => $browser]);
        $this->put(self::LOGINS, json_encode(array_slice($list, 0, 10)));
    }

    /** @return array<int, array{at: string, ip: string, browser: string}> */
    public function recentLogins(): array
    {
        return json_decode((string) $this->get(self::LOGINS), true) ?: [];
    }

    private function get(string $key): ?string
    {
        try {
            return DB::table('admin_settings')->where('key', $key)->value('value');
        } catch (\Throwable $e) {
            return null; // table not created yet
        }
    }

    private function put(string $key, string $value): void
    {
        DB::table('admin_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
    }

    private function forget(string $key): void
    {
        DB::table('admin_settings')->where('key', $key)->delete();
    }
}
