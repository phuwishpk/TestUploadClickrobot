<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $linkedStudentIds = $user->parentStudents()->pluck('student_id')->toArray();

        if (empty($linkedStudentIds)) {
            return view('parent.media.index', [
                'media' => collect(),
                'selectedStudentId' => null,
                'students' => collect()
            ]);
        }

        $selectedStudentId = $request->get('student_id');
        $students = \App\Models\Student::whereIn('id', $linkedStudentIds)->with('classroom')->get();

        $query = Media::whereIn('student_id', $linkedStudentIds)
            ->with(['student.classroom', 'uploader']);

        if ($selectedStudentId && in_array($selectedStudentId, $linkedStudentIds)) {
            $query->where('student_id', $selectedStudentId);
        }

        $media = $query->latest()->paginate(20);

        return view('parent.media.index', compact('media', 'selectedStudentId', 'students'));
    }

    public function show(Request $request, Media $media)
    {
        $user = $request->user();
        $linkedStudentIds = $user->parentStudents()->pluck('student_id')->toArray();

        if (!in_array($media->student_id, $linkedStudentIds)) {
            abort(403, 'คุณไม่มีสิทธิ์ดูไฟล์นี้');
        }

        $media->load(['student.classroom', 'uploader']);

        return view('parent.media.show', compact('media'));
    }
}
