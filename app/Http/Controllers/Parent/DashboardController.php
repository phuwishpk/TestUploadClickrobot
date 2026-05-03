<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $linkedStudents = $user->parentStudents()->with('student.classroom')->get();

        $studentIds = $linkedStudents->pluck('student_id')->toArray();

        $stats = [
            'children_count' => count($linkedStudents),
            'media_count' => \App\Models\Media::whereIn('student_id', $studentIds)->count(),
        ];

        $recentMedia = \App\Models\Media::with(['student.classroom'])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->limit(10)
            ->get();

        return view('parent.dashboard', compact('stats', 'recentMedia', 'linkedStudents'));
    }
}
