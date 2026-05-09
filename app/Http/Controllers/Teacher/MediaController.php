<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;
use App\Models\Student;
use App\Models\Classroom;
use App\Services\MediaCompressor;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    protected MediaCompressor $compressor;

    public function __construct(MediaCompressor $compressor)
    {
        $this->compressor = $compressor;
    }

    public function create(Request $request)
    {
        $classrooms = $request->user()->classrooms()->with('students')->get();
        $classroomId = $request->get('classroom_id');
        $selectedClassroom = null;
        $students = collect();

        if ($classroomId) {
            $selectedClassroom = $classrooms->find($classroomId);
            if ($selectedClassroom) {
                $students = $selectedClassroom->students;
            }
        }

        return view('teacher.upload', compact('classrooms', 'selectedClassroom', 'students'));
    }

    public function store(Request $request)
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
        
        if (!$request->user()->classrooms()->where('id', $classroom->id)->exists()) {
            abort(403);
        }

        $uploadDate = $validated['upload_date'] ?? now()->format('Y-m-d');
        $uploadedCount = 0;
        $errors = [];
        $totalFiles = count($validated['student_ids']) * count($request->file('files'));
        $processedFiles = 0;

        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::findOrFail($studentId);
            
            foreach ($request->file('files') as $file) {
                try {
                    $result = $this->compressor->compress($file, $student, $classroom, $uploadDate);
                    
                    Media::create([
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
                    
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "ไฟล์ {$file->getClientOriginalName()} สำหรับ {$student->name}: " . $e->getMessage();
                    \Log::error('Upload error', [
                        'file' => $file->getClientOriginalName(),
                        'student' => $student->name,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $processedFiles++;
            }
        }
        
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => $uploadedCount > 0,
                'message' => empty($errors) 
                    ? "อัปโหลดสำเร็จ {$uploadedCount} ไฟล์"
                    : "อัปโหลดสำเร็จ {$uploadedCount} ไฟล์ มีข้อผิดพลาด " . count($errors) . " รายการ",
                'count' => $uploadedCount,
                'errors' => $errors,
                'processed_files' => $processedFiles,
                'total_files' => $totalFiles
            ], 200);
        }
        
        return redirect()->route('teacher.upload.create', ['classroom_id' => $classroom->id])
            ->with('success', "อัปโหลดสำเร็จ {$uploadedCount} ไฟล์");
    }

    public function destroy(Request $request, Media $media)
    {
        $user = $request->user();

        // ตรวจสอบว่า media อยู่ใน classroom ที่ครูเป็นเจ้าของ
        if (!$user->classrooms()->where('classrooms.id', $media->classroom_id)->exists()) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $media->delete();

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'ลบสำเร็จ']);
        }

        return redirect()->back()->with('success', 'ลบไฟล์สำเร็จ');
    }
}
