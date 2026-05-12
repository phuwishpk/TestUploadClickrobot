<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentStudent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ResetSchoolData extends Command
{
    protected $signature = 'school:reset {--seed : Also seed test data}';
    protected $description = 'Reset all schools and related data, optionally seed with test data';

    public function handle(): int
    {
        $this->info('Resetting school data...');
        $this->newLine();

        // Drop school databases first
        $schools = School::all();
        foreach ($schools as $school) {
            $dbName = $school->getDatabaseName();
            try {
                \DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
                $this->info("✓ Dropped database: {$dbName}");
            } catch (\Exception $e) {
                $this->warn("Could not drop {$dbName}: " . $e->getMessage());
            }
        }

        // Truncate tables in master database
        $this->info('Truncating master tables...');
        Schema::disableForeignKeyConstraints();

        try {
            ParentStudent::truncate();
            Student::truncate();
            Classroom::truncate();
            User::where('role', '!=', 'admin')->delete();
            School::truncate();
            $this->info('✓ Truncated all tables');
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        if ($this->option('seed')) {
            $this->info('Seeding test data...');
            $this->call('db:seed', ['--class' => 'SchoolTestSeeder']);
        }

        $this->newLine();
        $this->info('School data reset complete!');
        $this->info('');
        $this->info('Test Accounts:');
        $this->table(
            ['Role', 'Email', 'Password', 'School'],
            [
                ['admin', 'superadmin@school.com', '12345', '-'],
                ['school_admin', 'admin_bnk@school.com', '12345', 'bnk (Bangrak)'],
                ['school_admin', 'admin_srb@school.com', '12345', 'srb (Saraburi)'],
                ['school_admin', 'admin_nbr@school.com', '12345', 'nbr (Nonthaburi)'],
                ['teacher', 'teacher_bnk@school.com', '12345', 'bnk (Bangrak)'],
                ['teacher', 'teacher_srb@school.com', '12345', 'srb (Saraburi)'],
            ]
        );

        return Command::SUCCESS;
    }
}
