<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Student;
use App\Models\Classroom;
use App\Services\MediaCompressor;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    protected MediaCompressor $compressor;

    public function __construct(MediaCompressor $compressor)
    {
        $this->compressor = $compressor;
    }

    public function create()
    {
        $classrooms = auth()->user()->school->classrooms()->with('students')->get();
        return view('school_admin.upload', compact('classrooms'));
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

        abort_unless($classroom->school_id === auth()->user()->school_id, 403);

        // Set school for correct R2 bucket
        if ($classroom->school) {
            $this->compressor->setSchool($classroom->school);
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
                        'original_size' => $result['original_size'] ?? null,
                        'compression_saved_bytes' => $result['compression_saved_bytes'] ?? null,
                        'compression_reduction_percent' => $result['compression_reduction_percent'] ?? null,
                        'uploaded_by' => auth()->id(),
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

        return redirect()->back()
            ->with($uploadedCount > 0 ? 'success' : 'error',
                empty($errors)
                    ? "อัปโหลดสำเร็จ {$uploadedCount} ไฟล์"
                    : "อัปโหลดสำเร็จ {$uploadedCount} ไฟล์ มีข้อผิดพลาด " . count($errors) . " รายการ"
            );
    }
}
