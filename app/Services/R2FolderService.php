<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Student;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

class R2FolderService
{
    protected ?S3Client $s3Client = null;
    protected ?School $currentSchool = null;

    public function __construct()
    {
        $this->initS3Client();
    }

    /**
     * Set the current school for bucket selection
     */
    public function setSchool(School $school): self
    {
        $this->currentSchool = $school;
        return $this;
    }

    /**
     * Get the current R2 bucket name
     */
    protected function getBucket(): string
    {
        if ($this->currentSchool) {
            return $this->currentSchool->getR2Bucket();
        }
        return config('filesystems.disks.r2.bucket', 'school-uploads');
    }

    protected function initS3Client(): void
    {
        if (!config('filesystems.disks.r2')) {
            return;
        }

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.r2.region', 'auto'),
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.r2.key'),
                'secret' => config('filesystems.disks.r2.secret'),
            ],
        ]);
    }

    public function createSchoolFolder(School $school): bool
    {
        $folderSlug = $school->slug;

        Log::info('Creating school folder', [
            'school_id' => $school->id,
            'folder_slug' => $folderSlug,
            'bucket' => $school->getR2Bucket(),
        ]);

        // Set current school for bucket selection
        $this->setSchool($school);

        return $this->createFolder($folderSlug, [
            'school_id' => $school->id,
            'school_name' => $school->name,
            'created_at' => $school->created_at->toIso8601String(),
        ]);
    }

    public function createClassroomFolder(Classroom $classroom): bool
    {
        $folderSlug = $classroom->folder_slug;

        Log::info('Creating classroom folder', [
            'classroom_id' => $classroom->id,
            'folder_slug' => $folderSlug,
        ]);

        if ($classroom->school) {
            $this->setSchool($classroom->school);
            $this->createSchoolFolder($classroom->school);
        }

        return $this->createFolder($folderSlug, [
            'classroom_id' => $classroom->id,
            'classroom_name' => $classroom->name,
            'school_id' => $classroom->school_id,
            'created_at' => $classroom->created_at->toIso8601String(),
        ]);
    }

    public function createStudentFolder(Classroom $classroom, Student $student): string
    {
        // Set school for bucket
        if ($classroom->school) {
            $this->setSchool($classroom->school);
        }

        $folderSlug = $classroom->folder_slug;
        $studentFolder = $this->getStudentFolder($classroom, $student);
        $fullPath = "{$folderSlug}/{$studentFolder}";

        Log::info('Creating student folder', [
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'folder' => $fullPath,
            'bucket' => $this->getBucket(),
        ]);

        return $this->createFolder($fullPath, [
            'student_id' => $student->id,
            'student_code' => $student->code,
            'classroom_id' => $classroom->id,
        ]) ? $fullPath : '';
    }

    public function getStudentFolder(Classroom $classroom, Student $student): string
    {
        // Sanitize student code for R2 compatibility - use only ASCII
        $code = preg_replace('/[^a-zA-Z0-9]/', '_', $student->code);
        $code = preg_replace('/_+/', '_', $code);
        $code = trim($code, '_');
        return sprintf('STU_%d_%s', $student->id, $code);
    }

    public function getDateFolder(string $uploadDate): string
    {
        $dateObj = \Carbon\Carbon::parse($uploadDate);
        return $dateObj->format('dmy');
    }

    public function getFullPath(Classroom $classroom, Student $student, string $filename, ?string $uploadDate = null): string
    {
        $folderSlug = $classroom->folder_slug;
        $studentFolder = $this->getStudentFolder($classroom, $student);
        $dateFolder = $uploadDate ? $this->getDateFolder($uploadDate) : now()->format('dmy');

        return sprintf('%s/%s/%s/%s',
            $folderSlug,
            $dateFolder,
            $studentFolder,
            $filename
        );
    }

    public function getBasePath(Classroom $classroom, ?string $uploadDate = null): string
    {
        $folderSlug = $classroom->folder_slug;
        $dateFolder = $uploadDate ? $this->getDateFolder($uploadDate) : now()->format('dmy');
        return sprintf('%s/%s', $folderSlug, $dateFolder);
    }

    protected function createFolder(string $path, array $metadata = []): bool
    {
        if ($this->s3Client) {
            return $this->createR2Folder($path, $metadata);
        }

        return $this->createLocalFolder($path, $metadata);
    }

    protected function createR2Folder(string $path, array $metadata = []): bool
    {
        try {
            $bucket = $this->getBucket();
            
            $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => "{$path}/.folder.meta",
                'Body' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'ContentType' => 'application/json',
                'ACL' => 'public-read',
            ]);

            Log::info('R2 folder created', ['path' => $path, 'bucket' => $bucket]);
            return true;
        } catch (\Exception $e) {
            Log::error('R2 folder creation failed', [
                'path' => $path,
                'bucket' => $this->getBucket(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function createLocalFolder(string $path, array $metadata = []): bool
    {
        $basePath = storage_path('app/uploads');
        $fullPath = "{$basePath}/{$path}";
        $metaPath = "{$fullPath}/.folder.meta";

        $parts = explode('/', $fullPath);
        $current = '';
        foreach ($parts as $part) {
            if (empty($part)) continue;
            $current .= '/' . $part;
            if (!is_dir($current)) {
                @mkdir($current, 0755);
            }
        }

        if (!empty($metadata)) {
            file_put_contents($metaPath, json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        return is_dir($fullPath);
    }

    public function folderExists(string $path): bool
    {
        if ($this->s3Client) {
            try {
                $bucket = $this->getBucket();
                $this->s3Client->headObject([
                    'Bucket' => $bucket,
                    'Key' => "{$path}/.folder.meta",
                ]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        $basePath = storage_path('app/uploads');
        return is_dir("{$basePath}/{$path}");
    }
}
