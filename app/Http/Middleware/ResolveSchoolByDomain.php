<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ResolveSchoolByDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Extract school slug from path (e.g., /school/bangrak/teacher/dashboard)
        $schoolSlug = $request->route('school');

        // Fallback: try subdomain if no path parameter
        if (!$schoolSlug) {
            $schoolSlug = $this->extractSlug($host);
        }

        $school = null;

        // IMPORTANT: School lookup MUST use master connection (not school-specific DB)
        // because schools table only exists in the master database
        // Always resolve school from URL slug first (authoritative), not from user's school_id
        if ($schoolSlug) {
            // URL has school slug — use it (authoritative source)
            $school = School::on('mysql')->where('slug', $schoolSlug)->first();

            if (!$school) {
                abort(404, 'School not found: ' . $schoolSlug);
            }
        } elseif ($request->user()) {
            // No school in URL — use user's school_id
            if ($request->user()->school_id) {
                $school = School::on('mysql')->find($request->user()->school_id);
            }

            if (!$school) {
                $school = School::on('mysql')->where('is_active', true)->first();
            }
        } else {
            // No school in URL and no user — use session or default
            $schoolId = $request->session()->get('school_id');

            if ($schoolId) {
                $school = School::on('mysql')->find($schoolId);
            }

            if (!$school) {
                $school = School::on('mysql')->where('is_active', true)->first();
            }
        }

        if ($school) {
            // Store school in request attributes
            $request->attributes->set('school', $school);
            $request->attributes->set('school_id', $school->id);

            // Store school in session for persistence
            $request->session()->put('school_id', $school->id);

            // NOTE: Database switching is disabled for now
            // To enable per-school databases, run: php artisan schools:sync-schemas
            // Then uncomment the code below:
            //
            // if ($school->db_host) {
            //     $isAdmin = $request->user()?->role === 'admin';
            //     if (!$isAdmin) {
            //         $this->switchToSchoolDatabase($school);
            //     }
            // }

            // Switch R2 bucket to school-specific bucket if configured
            if ($school->r2_bucket) {
                config(['filesystems.disks.r2.bucket' => $school->r2_bucket]);
            }
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

    private function switchToSchoolDatabase(School $school): void
    {
        $connectionName = "school_{$school->id}";

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

        // Set as default connection for this request
        config(['database.default' => $connectionName]);
    }
}
