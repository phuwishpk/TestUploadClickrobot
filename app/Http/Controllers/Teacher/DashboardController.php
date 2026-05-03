<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Media;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'classrooms' => $user->classrooms()->count(),
            'students' => Student::whereIn('classroom_id', $user->classrooms()->pluck('id'))->count(),
            'media_count' => Media::whereIn('classroom_id', $user->classrooms()->pluck('id'))->count(),
        ];

        $recentMedia = Media::with(['student', 'classroom'])
            ->whereIn('classroom_id', $user->classrooms()->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('teacher.dashboard', compact('stats', 'recentMedia'));
    }
}
