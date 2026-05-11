<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswords extends Command
{
    protected $signature = 'users:reset-passwords {emails?* : Email addresses to reset}';
    protected $description = 'Reset user passwords to default (12345)';

    public function handle(): int
    {
        $emails = $this->argument('emails');

        if (empty($emails)) {
            // Reset all users
            $users = User::all();
            $this->info('Resetting all user passwords...');
        } else {
            $users = User::whereIn('email', $emails)->get();
            if ($users->isEmpty()) {
                $this->error('No users found with the provided emails.');
                return 1;
            }
            $this->info('Resetting passwords for ' . $users->count() . ' user(s)...');
        }

        $defaultPassword = '12345';
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->password = Hash::make($defaultPassword);
            $user->save();
            $this->line(" {$user->email} ({$user->role})");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("All passwords have been reset to: {$defaultPassword}");

        return 0;
    }
}
