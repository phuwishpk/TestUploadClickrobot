<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DebugRouteParams
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log request info
        $debug = [
            'timestamp' => date('Y-m-d H:i:s'),
            'host' => $request->getHost(),
            'uri' => $request->getRequestUri(),
            'route_name' => $request->route()?->getName(),
            'route_params' => $request->route()?->parameters(),
        ];
        file_put_contents('/tmp/route_debug.log', json_encode($debug) . "\n", FILE_APPEND);

        return $next($request);
    }
}
