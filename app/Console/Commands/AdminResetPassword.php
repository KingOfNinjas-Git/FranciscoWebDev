<?php

namespace App\Console\Commands;

use App\Services\AdminPassword;
use Illuminate\Console\Command;

class AdminResetPassword extends Command
{
    protected $signature = 'admin:reset-password';

    protected $description = 'Forgot the admin password? Goes back to the ADMIN_PASSWORD line in .env and signs every browser out';

    public function handle(AdminPassword $passwords): int
    {
        $passwords->resetToEnv();

        $this->info('The saved password was removed. The admin now accepts the ADMIN_PASSWORD from .env again.');
        $this->line('Sign in with it, then set a new password on the Security page.');

        return self::SUCCESS;
    }
}
