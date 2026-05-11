<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveSchoolByDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Extract domain e.g. bangrak from bangrak.school.com
        $domain = $this->extractDomain($host);

        // If no subdomain detected, continue normally
        if (!$domain) {
            return $next($request);
        }

        // Look up school from master database (mysql connection)
        $school = School::on('mysql')->where('domain', $domain)->first();

        if (!$school) {
            abort(404, 'School not found');
        }

        // Store school in request attributes for later use
        $request->attributes->set('school', $school);

        // Switch database connection to school-specific database
        $this->switchToSchoolDatabase($school->id, $school->getDatabaseName());

        // Switch R2 bucket to school-specific bucket
        config(['filesystems.disks.r2.bucket' => $school->getR2Bucket()]);

        return $next($request);
    }

    private function extractDomain(string $host): ?string
    {
        // main.school.com -> bangrak (strip school.com)
        $baseDomain = config('app.base_domain', 'school.com');

        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = substr($host, 0, -strlen('.' . $baseDomain));
            return $subdomain ?: null;
        }

        // localhost: bangrak.localhost -> bangrak
        if (preg_match('/^([a-z0-9-]+)\.localhost(?::\d+)?$/i', $host, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function switchToSchoolDatabase(int $schoolId, string $dbName): void
    {
        $connectionName = "school_{$schoolId}";

        // Add connection if not exists
        if (!DB::connection($connectionName)->getConfig('host')) {
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

        // Set as default connection
        config(['database.default' => $connectionName]);
    }
}
