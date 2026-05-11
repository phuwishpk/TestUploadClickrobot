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

        file_put_contents('/tmp/route_debug.log', json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'controller' => 'Teacher\DashboardController',
            'user_id' => $user->id ?? null,
            'user_role' => $user->role ?? null,
            'user_school_id' => $user->school_id ?? null,
            'uri' => $request->getRequestUri(),
        ]) . "\n", FILE_APPEND);

        $stats = [
            'classrooms' => $user->classrooms()->count(),
            'students' => Student::whereIn('classroom_id', $user->classrooms()->pluck('id'))->count(),
            'media_count' => Media::whereIn('classroom_id', $user->classrooms()->pluck('id'))->count(),
        ];

        $recentMedia = Media::with(['student', 'classroom'])
            ->whereIn('classroom_id', $user->classrooms()->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $compressionStats = $this->compressionStatsFromLog();
        $recentMedia->each(function (Media $media) use ($compressionStats) {
            if ($media->original_size !== null || !$compressionStats->has($media->size)) {
                return;
            }

            $stats = $compressionStats->get($media->size);

            $media->setAttribute('original_size', $stats['original_size']);
            $media->setAttribute('compression_saved_bytes', $stats['saved_bytes']);
            $media->setAttribute('compression_reduction_percent', $stats['reduction_percent']);
        });

        return view('teacher.dashboard', compact('stats', 'recentMedia'));
    }

    private function compressionStatsFromLog(): \Illuminate\Support\Collection
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return collect();
        }

        preg_match_all(
            '/(?:Image compression(?: result| \(JPEG(?: fallback)?\))|Video compression result) (\{.*?\})/',
            file_get_contents($logPath),
            $matches
        );

        return collect($matches[1])
            ->map(fn (string $json) => json_decode($json, true))
            ->filter(fn (?array $stats) => isset(
                $stats['original_size'],
                $stats['compressed_size'],
                $stats['saved_bytes'],
                $stats['reduction_percent']
            ))
            ->keyBy('compressed_size');
    }
}
