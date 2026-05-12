<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncSchoolSchemas extends Command
{
    protected $signature = 'schools:sync-schemas {--school= : Specific school ID or slug}';
    protected $description = 'Sync master database schema to all school databases';

    public function handle(): int
    {
        $schoolId = $this->option('school');

        // Get schools with db_host configured
        $query = \App\Models\School::whereNotNull('db_host');
        if ($schoolId) {
            if (is_numeric($schoolId)) {
                $query->where('id', $schoolId);
            } else {
                $query->where('slug', $schoolId);
            }
        }

        $schools = $query->get();

        if ($schools->isEmpty()) {
            $this->warn('No schools with separate databases found.');
            return self::SUCCESS;
        }

        // Tables that need to be synced to school databases
        $tables = [
            'users',
            'classrooms',
            'students',
            'parent_students',
            'classroom_student',
            'media',
        ];

        foreach ($schools as $school) {
            $this->info("Processing school: {$school->name} ({$school->slug})");

            $connectionName = "school_{$school->id}";

            // Create connection if not exists
            if (!config("database.connections.{$connectionName}")) {
                config([
                    "database.connections.{$connectionName}" => [
                        'driver' => 'mysql',
                        'host' => $school->db_host,
                        'port' => 3306,
                        'database' => $school->database_name,
                        'username' => 'root',
                        'password' => 'root_secret',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => true,
                        'engine' => null,
                    ],
                ]);
            }

            try {
                // Check if connection works
                DB::connection($connectionName)->getPdo();
            } catch (\Exception $e) {
                $this->error("Cannot connect to {$school->database_name}: " . $e->getMessage());
                continue;
            }

            // Sync each table
            foreach ($tables as $table) {
                $this->syncTable($connectionName, $table);
            }
        }

        $this->info('Done!');
        return self::SUCCESS;
    }

    private function syncTable(string $connectionName, string $table): void
    {
        // Check if table exists in master
        if (!Schema::hasTable($table)) {
            $this->warn("  Table {$table} does not exist in master database, skipping.");
            return;
        }

        // Check if table exists in school DB
        if (!Schema::connection($connectionName)->hasTable($table)) {
            $this->line("  Creating table {$table}...");
            $this->createTableFromMaster($connectionName, $table);
        } else {
            $this->line("  Table {$table} already exists, skipping.");
        }
    }

    private function createTableFromMaster(string $connectionName, string $table): void
    {
        // Get CREATE TABLE statement from master
        $createSql = DB::selectOne("SHOW CREATE TABLE `{$table}`");

        if (!$createSql) {
            $this->error("  Cannot get CREATE TABLE for {$table}");
            return;
        }

        $sql = $createSql->{'Create Table'};

        // Replace master database name with school database name
        $masterDb = config('database.connections.mysql.database');
        $schoolDb = config("database.connections.{$connectionName}.database");
        $sql = str_replace($masterDb . '.', '', $sql);
        $sql = str_replace("CREATE TABLE `{$table}`", "CREATE TABLE IF NOT EXISTS `{$table}`", $sql);

        try {
            DB::connection($connectionName)->statement($sql);
            $this->info("  Created table {$table}");
        } catch (\Exception $e) {
            $this->error("  Failed to create {$table}: " . $e->getMessage());
        }
    }
}
