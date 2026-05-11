<?php

use App\Models\School;

/**
 * Generate dashboard URL based on user role.
 * For subdomain-based roles (teacher/student/parent/school_admin),
 * builds URL with school subdomain.
 */
function dashboard_url($role, $schoolId = null): string
{
    // Admin uses named route (no subdomain needed)
    if ($role === 'admin') {
        return route('admin.dashboard');
    }

    // For subdomain-based roles, build URL manually
    $school = null;
    if ($schoolId) {
        $school = School::find($schoolId);
    }

    if (!$school) {
        $school = School::first();
    }

    if (!$school || !$school->slug) {
        return '/';
    }

    $baseDomain = config('app.base_domain', 'localhost');
    $port = config('app.port', '8080');
    $protocol = request()->secure() ? 'https' : 'http';

    $path = match ($role) {
        'school_admin' => 'school-admin/dashboard',
        'teacher' => 'teacher/dashboard',
        'parent' => 'parent/dashboard',
        'student' => 'student/dashboard',
        default => '',
    };

    return "{$protocol}://{$school->slug}.{$baseDomain}:{$port}/{$path}";
}
