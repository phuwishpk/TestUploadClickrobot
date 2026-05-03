<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return view('student.dashboard', [
                'stats' => ['media_count' => 0],
                'recentMedia' => collect(),
                'student' => null
            ]);
        }

        $stats = [
            'media_count' => $student->media()->count(),
        ];

        $recentMedia = $student->media()
            ->with(['classroom', 'uploader'])
            ->latest()
            ->limit(10)
            ->get();

        return view('student.dashboard', compact('stats', 'recentMedia', 'student'));
    }
}
