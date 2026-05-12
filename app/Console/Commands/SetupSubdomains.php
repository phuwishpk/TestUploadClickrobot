<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupSubdomains extends Command
{
    protected $signature = 'setup:subdomains {--dry-run : Show what would be added without making changes}';
    protected $description = 'Setup subdomain entries in /etc/hosts for local development';

    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('  Setup Subdomains for Local Development');
        $this->info('==========================================');
        $this->newLine();

        $entries = implode("\n", [
            '',
            '# School Media Upload - Subdomains',
            '127.0.0.1    admin.localhost',
            '127.0.0.1    bnk.localhost',
            '127.0.0.1    srb.localhost',
            '127.0.0.1    nbr.localhost',
        ]);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No changes made');
            $this->info('Would add these lines to /etc/hosts:');
            $this->line($entries);
            return Command::SUCCESS;
        }

        $hostsFile = '/etc/hosts';

        // Check if entries already exist
        $content = file_get_contents($hostsFile);
        if (str_contains($content, 'admin.localhost')) {
            $this->warn('Subdomains already exist in /etc/hosts');
            return Command::SUCCESS;
        }

        // Append entries
        if (file_put_contents($hostsFile, $entries, FILE_APPEND | LOCK_EX)) {
            $this->info('✓ Added subdomains to ' . $hostsFile);

            $this->newLine();
            $this->info('Flush DNS cache:');
            $this->line('  macOS: sudo dscacheutil -flushcache && sudo killall -HUP mDNSResponder');
            $this->line('  Linux:  sudo systemd-resolve --flush-caches');

            $this->newLine();
            $this->info('You can now access:');
            $this->table(
                ['URL', 'Description'],
                [
                    ['http://localhost:8080', 'Login Page'],
                    ['http://admin.localhost:8080', 'Admin'],
                    ['http://bnk.localhost:8080', 'School 1 - Bangrak'],
                    ['http://srb.localhost:8080', 'School 2 - Saraburi'],
                    ['http://nbr.localhost:8080', 'School 3 - Nonthaburi'],
                ]
            );

            return Command::SUCCESS;
        }

        $this->error('Failed to write to ' . $hostsFile);
        $this->line('Try running with sudo: sudo php artisan setup:subdomains');

        return Command::FAILURE;
    }
}
