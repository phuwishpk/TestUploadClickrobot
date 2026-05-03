<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'ไม่พบข้อมูลนักเรียน');
        }

        $media = $student->media()
            ->with(['classroom', 'uploader'])
            ->latest()
            ->paginate(20);

        return view('student.media.index', compact('media', 'student'));
    }

    public function show(Request $request, Media $media)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $media->student_id !== $student->id) {
            abort(403, 'คุณไม่มีสิทธิ์ดูไฟล์นี้');
        }

        $media->load(['classroom', 'uploader']);

        return view('student.media.show', compact('media'));
    }
}
