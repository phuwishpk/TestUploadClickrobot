<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugRouteResolution
{
    public function handle(Request $request, Closure $next): Response
    {
        $debug = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'request_start',
            'host' => $request->getHost(),
            'uri' => $request->getRequestUri(),
            'session_school_id' => $request->session()->get('school_id'),
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
        ];

        // Log to file inside container
        $logPath = storage_path('logs/url_debug.log');
        file_put_contents($logPath, json_encode($debug) . "\n", FILE_APPEND);

        $response = $next($request);

        // Log response
        $logPath = storage_path('logs/url_debug.log');
        file_put_contents($logPath, json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'response_ready',
            'status' => $response->getStatusCode(),
            'uri' => $request->getRequestUri(),
        ]) . "\n", FILE_APPEND);

        return $response;
    }
}
