<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaCompressor;
use Illuminate\Http\Request;
use App\Models\Classroom;

class MediaController extends Controller
{
    public function create()
    {
        $classrooms = auth()->user()->school->classrooms()->with('students')->get();
        return view('school_admin.upload', compact('classrooms'));
    }

    public function store(Request $request, MediaCompressor $compressor)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'media_type' => 'required|in:photo,video',
            'files.*' => 'required|file|max:204800', // 200MB max
        ]);

        $classroom = Classroom::findOrFail($request->classroom_id);

        abort_unless($classroom->school_id === auth()->user()->school_id, 403);

        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            $result = $compressor->processAndUpload($file, $classroom, $request->media_type);

            if ($result['success']) {
                $uploadedMedia[] = $result['media'];
            }
        }

        $successCount = count($uploadedMedia);
        $failCount = count($request->file('files')) - $successCount;

        $message = "อัปโหลดสำเร็จ {$successCount} ไฟล์";
        if ($failCount > 0) {
            $message .= ", ล้มเหลว {$failCount} ไฟล์";
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $message);
    }
}
