<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;

class TestController extends Controller
{
    public function test()
    {
        $params = URL::getDefaultParameters();
        $school = request()->route()?->parameter('school');
        $host = request()->getHost();

        return response()->json([
            'host' => $host,
            'route_school_param' => $school,
            'url_defaults' => $params,
            'session_school_id' => request()->session()->get('school_id'),
        ]);
    }
}
