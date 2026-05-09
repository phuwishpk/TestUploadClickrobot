<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Student;
use App\Models\Classroom;
use App\Services\MediaCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    protected MediaCompressor $compressor;

    public function __construct(MediaCompressor $compressor)
    {
        $this->compressor = $compressor;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $studentId = $request->get('student_id');

        $accessibleIds = $user->getAccessibleStudentIds();

        if (empty($accessibleIds)) {
            return response()->json([]);
        }

        $query = Media::whereIn('student_id', $accessibleIds)
            ->with(['student.classroom', 'uploader']);

        if ($studentId && in_array($studentId, $accessibleIds)) {
            $query->where('student_id', $studentId);
        }

        $media = $query->latest()->paginate(20);

        return response()->json($media);
    }

    public function show(Request $request, Media $media)
    {
        $user = $request->user();
        $accessibleIds = $user->getAccessibleStudentIds();

        if (!in_array($media->student_id, $accessibleIds)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $media->load(['student.classroom', 'uploader']);

        return response()->json($media);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,mpeg|max:204800',
            'upload_date' => 'nullable|date',
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);
        
        if ($classroom->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $uploadDate = $validated['upload_date'] ?? now()->format('Y-m-d');
        $uploadedMedia = [];

        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::findOrFail($studentId);
            
            // ตรวจสอบว่านักเรียนอยู่ใน classroom ที่เลือก (ผ่าน pivot table)
            if (!$student->classrooms()->where('classrooms.id', $classroom->id)->exists()) {
                continue;
            }
            
            foreach ($request->file('files') as $file) {
                $result = $this->compressor->compress($file, $student, $classroom, $uploadDate);
                
                $media = Media::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'type' => $result['type'],
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $result['filename'],
                    'path' => $result['path'],
                    'thumbnail_path' => $result['thumbnail_path'] ?? null,
                    'mime_type' => $result['mime_type'],
                    'size' => $result['size'],
                    'uploaded_by' => $request->user()->id,
                    'uploaded_date' => $uploadDate,
                ]);

                $uploadedMedia[] = $media;
            }
        }

        return response()->json([
            'message' => 'Upload successful',
            'count' => count($uploadedMedia),
            'media' => $uploadedMedia,
        ], 201);
    }

    public function stream(Request $request, Media $media)
    {
        $user = $request->user();
        $accessibleIds = $user->getAccessibleStudentIds();

        if (!in_array($media->student_id, $accessibleIds)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $disk = config('filesystems.default');
        $path = $media->path;

        if (!Storage::disk($disk)->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $file = Storage::disk($disk)->get($path);
        $mimeType = $media->mime_type;

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $media->original_name . '"');
    }

    public function destroy(Request $request, Media $media)
    {
        $user = $request->user();
        $accessibleIds = $user->getAccessibleStudentIds();

        if (!in_array($media->student_id, $accessibleIds)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $media->delete();

        return response()->json(['message' => 'ลบสำเร็จ']);
    }
}
