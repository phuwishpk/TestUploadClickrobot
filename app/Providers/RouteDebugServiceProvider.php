<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteDebugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Listen for route missing events
        Route::missing(function ($request, $exception) {
            file_put_contents('/tmp/route_debug.log', json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'event' => 'route_missing',
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'uri' => $request->getRequestUri(),
                'host' => $request->getHost(),
            ]) . "\n", FILE_APPEND);
        });
    }
}
