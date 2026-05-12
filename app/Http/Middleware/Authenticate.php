<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Always redirect to main domain login (no subdomain)
        $baseDomain = config('app.base_domain', 'localhost');
        $port = config('app.port', '8080');

        return "http://{$baseDomain}:{$port}/login";
    }
}
