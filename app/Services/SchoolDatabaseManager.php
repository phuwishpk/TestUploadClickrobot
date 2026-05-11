<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolDatabaseManager
{
    public function connectToSchool(int $schoolId): void
    {
        $school = School::on('mysql')->findOrFail($schoolId);
        $dbName = $school->getDatabaseName();

        $this->addConnectionIfNotExists($schoolId, $dbName);
        config(['database.default' => "school_{$schoolId}"]);
    }

    public function addConnectionIfNotExists(int $schoolId, string $dbName): void
    {
        $connectionName = "school_{$schoolId}";

        try {
            if (DB::connection($connectionName)->getConfig('host')) {
                return;
            }
        } catch (\Exception $e) {
            // Connection doesn't exist yet, proceed to add it
        }

        config([
            "database.connections.{$connectionName}" => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => $dbName,
                'username' => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);
    }

    public function createSchoolDatabase(School $school): void
    {
        $dbName = $school->getDatabaseName();

        // Create database on master connection
        DB::connection('mysql')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        Log::info("Database created: {$dbName}");

        // Add connection for the new database
        $this->addConnectionIfNotExists($school->id, $dbName);
    }

    public function runMigrationsForSchool(int $schoolId): void
    {
        $this->connectToSchool($schoolId);

        $connectionName = "school_{$schoolId}";

        \Artisan::call('migrate', [
            '--database' => $connectionName,
            '--force' => true,
        ]);

        Log::info("Migrations completed for school: {$schoolId}");
    }

    public function seedSchoolData(int $schoolId): void
    {
        $this->connectToSchool($schoolId);

        $connectionName = "school_{$schoolId}";

        \Artisan::call('db:seed', [
            '--database' => $connectionName,
            '--force' => true,
        ]);

        Log::info("Seeding completed for school: {$schoolId}");
    }

    public function provisionSchool(School $school): array
    {
        $results = [];

        // Step 1: Create database
        $this->createSchoolDatabase($school);
        $results['database'] = "Created: {$school->getDatabaseName()}";

        // Step 2: Run migrations
        $this->runMigrationsForSchool($school->id);
        $results['migrations'] = "Migrations completed for school: {$school->id}";

        // Step 3: Seed initial data (if needed)
        // $this->seedSchoolData($school->id);
        // $results['seeding'] = "Seeding completed for school: {$school->id}";

        return $results;
    }
}
