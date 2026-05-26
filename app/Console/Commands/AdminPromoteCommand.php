<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AdminPromoteCommand extends Command
{
    protected $signature = 'admin:promote {login : Username or email address}';

    protected $description = 'Grant site administrator privileges to a member account';

    public function handle(): int
    {
        $login = $this->argument('login');

        $user = User::query()
            ->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user) {
            $this->error("No user found for \"{$login}\".");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("{$user->username} ({$user->email}) is already an administrator.");

            return self::SUCCESS;
        }

        $user->forceFill(['is_admin' => true])->save();

        $this->info("Promoted {$user->username} ({$user->email}) to administrator.");

        return self::SUCCESS;
    }
}
