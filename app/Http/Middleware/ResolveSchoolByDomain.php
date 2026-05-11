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

        // Extract school slug from subdomain (e.g. bangrak from bangrak.localhost)
        $slug = $this->extractSlug($host);

        $school = null;

        if ($slug) {
            // Look up school by slug
            $school = School::where('slug', $slug)->first();

            if (!$school) {
                abort(404, 'School not found: ' . $slug);
            }
        } else {
            // No subdomain - try to get school from session
            $schoolId = $request->session()->get('school_id');

            if ($schoolId) {
                $school = School::find($schoolId);
            }

            // Fallback: use first active school
            if (!$school) {
                $school = School::where('is_active', true)->first();
            }
        }

        if (!$school) {
            abort(404, 'No school configured');
        }

        // Store school in request attributes
        $request->attributes->set('school', $school);
        $request->attributes->set('school_id', $school->id);

        // Switch to school-specific database if configured
        if ($school->database_name) {
            $this->switchToSchoolDatabase($school->id, $school->database_name);
        }

        // Switch R2 bucket to school-specific bucket if configured
        if ($school->r2_bucket) {
            config(['filesystems.disks.r2.bucket' => $school->r2_bucket]);
        }

        return $next($request);
    }

    private function extractSlug(string $host): ?string
    {
        // localhost: bangrak.localhost:8080 -> bangrak
        if (preg_match('/^([a-z0-9-]+)\.localhost(?::\d+)?$/i', $host, $matches)) {
            return $matches[1];
        }

        // e.g. bangrak.school.com -> bangrak
        $baseDomain = config('app.base_domain', 'school.com');

        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = substr($host, 0, -strlen('.' . $baseDomain));
            return $subdomain ?: null;
        }

        // e.g. school.com -> null (main domain)
        if ($host === $baseDomain || $host === 'www.' . $baseDomain) {
            return null;
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
