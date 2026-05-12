<?php

use App\Models\School;

/**
 * Generate dashboard URL based on user role using path-based school routing.
 */
function dashboard_url($role, $schoolId = null): string
{
    if ($role === 'admin') {
        return route('admin.dashboard');
    }

    $school = null;
    if ($schoolId) {
        $school = School::on('mysql')->find($schoolId);
    }

    if (!$school) {
        $school = School::on('mysql')->where('is_active', true)->first();
    }

    if (!$school || !$school->slug) {
        return '/';
    }

    $baseDomain = config('app.base_domain', 'localhost');
    $port = config('app.port', '8080');
    $protocol = request()->secure() ? 'https' : 'http';

    $path = match ($role) {
        'school_admin' => 'school-admin/dashboard',
        'teacher'      => 'teacher/dashboard',
        'parent'       => 'parent/dashboard',
        'student'      => 'student/dashboard',
        default        => '',
    };

    return "{$protocol}://{$baseDomain}:{$port}/school/{$school->slug}/{$path}";
}
