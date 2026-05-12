<?php

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

/**
 * Get current school slug from request
 */
function current_school_slug(): ?string
{
    return app('request')->route('schoolSlug')
        ?? app('request')->attributes->get('school')?->slug
        ?? session('current_school_slug');
}

/**
 * Generate a route URL with schoolSlug parameter
 *
 * @param string $name Route name
 * @param array|Model|null $parameters Route parameters (array) or a single Model for route model binding
 * @param bool $absolute Whether to generate absolute URL
 * @return string
 */
function school_route(string $name, array|Model|null $parameters = [], bool $absolute = true): string
{
    $isSchoolRoute = str_starts_with($name, 'teacher.')
        || str_starts_with($name, 'student.')
        || str_starts_with($name, 'parent.')
        || str_starts_with($name, 'school_admin.');

    // Convert Model to array format for route parameter binding
    if ($parameters instanceof Model) {
        $model = $parameters;
        $modelName = class_basename($model);
        $paramName = match ($modelName) {
            'Classroom' => 'classroom',
            'Student' => 'student',
            'Parent' => 'parent',
            'Teacher' => 'teacher',
            'Media' => 'media',
            'Link' => 'link',
            'School' => 'school',
            'User' => 'user',
            default => strtolower($modelName),
        };
        $parameters = [$paramName => $model];
    }

    $parameters = $parameters ?? [];

    if (!$isSchoolRoute) {
        return route($name, $parameters, $absolute);
    }

    $schoolSlug = $parameters['schoolSlug'] ?? current_school_slug();

    if (!$schoolSlug) {
        return '#';
    }

    $parameters['schoolSlug'] = $schoolSlug;

    try {
        $url = route($name, $parameters, false);
        $baseDomain = config('app.base_domain', 'localhost');
        $subdomainPattern = '/^' . preg_quote($schoolSlug, '/') . '\.' . preg_quote($baseDomain, '/') . '$/i';

        if (preg_match($subdomainPattern, request()->getHost())) {
            $url = preg_replace('#^/' . preg_quote($schoolSlug, '#') . '(/|$)#', '/', $url);

            if ($absolute) {
                $scheme = request()->secure() ? 'https' : 'http';
                $port = request()->getPort();
                $host = request()->getHost();
                $portSuffix = in_array($port, [80, 443], true) ? '' : ':' . $port;

                return "{$scheme}://{$host}{$portSuffix}{$url}";
            }

            return $url;
        }

        return route($name, $parameters, $absolute);
    } catch (\Exception $e) {
        return '#';
    }
}

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

    $path = match ($role) {
        'school_admin' => 'school-admin/dashboard',
        'teacher'      => 'teacher/dashboard',
        'parent'       => 'parent/dashboard',
        'student'      => 'student/dashboard',
        default        => '',
    };

    $host = request()->getHost();

    $baseDomain = config('app.base_domain', 'localhost');

    if (preg_match('/^' . preg_quote($school->slug, '/') . '\.' . preg_quote($baseDomain, '/') . '$/i', $host)) {
        return "/{$path}";
    }

    $port = config('app.port', '8080');
    $protocol = request()->secure() ? 'https' : 'http';

    return "{$protocol}://{$school->slug}.{$baseDomain}:{$port}/{$path}";
}
