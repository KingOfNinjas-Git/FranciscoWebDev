<?php

namespace App\Services;

use App\Support\Totp;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Optional two-factor login for the admin (authenticator app + one-time recovery codes).
 * The secret is stored encrypted in the admin_settings table.
 */
class TwoFactor
{
    private const SECRET = 'totp_secret';
    private const LAST_STEP = 'totp_last_step';
    private const RECOVERY = 'recovery_codes';

    /** Turned on in .env (ADMIN_2FA_DISABLED=true) if the phone and all recovery codes are lost. */
    public function bypassed(): bool
    {
        return (bool) config('admin.two_factor_disabled');
    }

    /** Is two-factor set up? (False, never an error, if the table has not been created yet.) */
    public function enabled(): bool
    {
        try {
            return $this->get(self::SECRET) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Must the login ask for a code? */
    public function required(): bool
    {
        return $this->enabled() && ! $this->bypassed();
    }

    public function newSecret(): string
    {
        return Totp::generateSecret();
    }

    /**
     * Turns two-factor on if the code from the app matches the new secret.
     *
     * @return string[]|null the recovery codes (shown once), or null if the code was wrong
     */
    public function confirmSetup(string $secret, string $code): ?array
    {
        $step = Totp::verify($secret, $code);
        if ($step === null) {
            return null;
        }

        $this->put(self::SECRET, Crypt::encryptString($secret));
        $this->put(self::LAST_STEP, (string) $step);

        return $this->regenerateRecoveryCodes();
    }

    /** Checks an authenticator code; each code works once. */
    public function verifyCode(string $code): bool
    {
        $secret = $this->secret();
        if ($secret === null) {
            return false;
        }

        $step = Totp::verify($secret, $code);
        if ($step === null || $step <= (int) $this->get(self::LAST_STEP)) {
            return false;
        }

        $this->put(self::LAST_STEP, (string) $step);

        return true;
    }

    /** Checks a recovery code; each works once. */
    public function useRecoveryCode(string $code): bool
    {
        $code = Str::lower(trim($code));
        $hashes = json_decode((string) $this->get(self::RECOVERY), true) ?: [];

        foreach ($hashes as $i => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashes[$i]);
                $this->put(self::RECOVERY, json_encode(array_values($hashes)));

                return true;
            }
        }

        return false;
    }

    /** Accepts either kind of code. */
    public function verifyAny(string $input): bool
    {
        return preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/i', trim($input))
            ? $this->useRecoveryCode($input)
            : $this->verifyCode($input);
    }

    /** @return string[] ten new codes (the old ones stop working) */
    public function regenerateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = Str::lower(Str::random(4) . '-' . Str::random(4));
        }

        $this->put(self::RECOVERY, json_encode(array_map(fn ($c) => Hash::make($c), $codes)));

        return $codes;
    }

    public function recoveryCodesLeft(): int
    {
        return count(json_decode((string) $this->get(self::RECOVERY), true) ?: []);
    }

    public function disable(): void
    {
        DB::table('admin_settings')->whereIn('key', [self::SECRET, self::LAST_STEP, self::RECOVERY])->delete();
    }

    private function secret(): ?string
    {
        $encrypted = $this->get(self::SECRET);

        return $encrypted ? Crypt::decryptString($encrypted) : null;
    }

    private function get(string $key): ?string
    {
        return DB::table('admin_settings')->where('key', $key)->value('value');
    }

    private function put(string $key, string $value): void
    {
        DB::table('admin_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
    }
}
