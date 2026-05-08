<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\Media;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaCompressor
{
    protected ImageManager $imageManager;
    protected string $uploadsPath;
    protected ?S3Client $s3Client = null;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->uploadsPath = '/var/www/html/storage/app/uploads';
        $this->initS3Client();
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

    public function compress(UploadedFile $file, Student $student, Classroom $classroom, string $uploadDate): array
    {
        $mimeType = $file->getMimeType();
        $type = $this->determineType($mimeType);
        
        $dateObj = \Carbon\Carbon::parse($uploadDate);
        $dateStr = $dateObj->format('dmy');
        $classroomName = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $classroom->name);
        
        $folderPath = sprintf('%s/%s', $classroomName . $dateStr, $student->code);
        $filename = $this->generateFilename($file);
        $fullPath = $folderPath . '/' . $filename;
        $absolutePath = $this->uploadsPath . '/' . $fullPath;
        
        // Debug logging
        \Log::info('MediaCompressor Debug', [
            'classroom_name' => $classroom->name,
            'classroomName_cleaned' => $classroomName,
            'student_code' => $student->code,
            'folderPath' => $folderPath,
            'absolutePath' => $absolutePath,
            'path_valid_utf8' => mb_check_encoding($absolutePath, 'UTF-8'),
        ]);

        if ($type === 'image') {
            return $this->compressImage($file, $absolutePath, $fullPath);
        }

        return $this->compressVideo($file, $absolutePath, $fullPath);
    }

    protected function determineType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'image';
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $timestamp = now()->timestamp;
        $random = substr(md5(uniqid()), 0, 6);
        $extension = $file->getClientOriginalExtension() ?: $this->getExtensionFromMime($file->getMimeType());
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    protected function getExtensionFromMime(string $mimeType): string
    {
        return match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            'video/webm' => 'webm',
            default => 'bin',
        };
    }

    protected function compressImage(UploadedFile $file, string $absolutePath, string $relativePath): array
    {
        $tempFile = $file->getPathname();
        
        // Create directory using Laravel's Filesystem
        $dir = dirname($absolutePath);
        
        if (!is_dir($dir)) {
            // Create parent directories one by one to handle btrfs quirks
            $parts = explode('/', $dir);
            $current = '';
            foreach ($parts as $i => $part) {
                if ($i === 0) {
                    $current = $part;
                } else {
                    $current .= '/' . $part;
                }
                if (!empty($part) && !is_dir($current)) {
                    @mkdir($current, 0755);
                }
            }
            
            if (!is_dir($dir)) {
                throw new \RuntimeException("Failed to create directory: {$dir}");
            }
            
            // Ensure www-data owns the directory
            @chown($dir, 'www-data');
            @chgrp($dir, 'www-data');
        }
        
        // Read and process image
        $image = $this->imageManager->read($tempFile);
        
        $maxWidth = 1920;
        $maxHeight = 1920;
        
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $quality = 80;
        
        // Save locally first
        $image->toJpeg($quality)->save($absolutePath);
        $size = filesize($absolutePath);

        // Upload to R2 if configured
        if ($this->s3Client) {
            $this->uploadToR2($absolutePath, $relativePath);
        }

        return [
            'type' => 'image',
            'filename' => basename($relativePath),
            'path' => $relativePath,
            'mime_type' => 'image/jpeg',
            'size' => $size,
        ];
    }

    protected function compressVideo(UploadedFile $file, string $absolutePath, string $relativePath): array
    {
        $tempInput = $file->getPathname();
        
        // Create directory using Laravel's Filesystem
        $dir = dirname($absolutePath);
        
        if (!is_dir($dir)) {
            // Create parent directories one by one to handle btrfs quirks
            $parts = explode('/', $dir);
            $current = '';
            foreach ($parts as $i => $part) {
                if ($i === 0) {
                    $current = $part;
                } else {
                    $current .= '/' . $part;
                }
                if (!empty($part) && !is_dir($current)) {
                    @mkdir($current, 0755);
                }
            }
            
            if (!is_dir($dir)) {
                throw new \RuntimeException("Failed to create directory: {$dir}");
            }
            
            @chown($dir, 'www-data');
            @chgrp($dir, 'www-data');
        }

        $ffmpegPath = '/usr/bin/ffmpeg';
        $tempOutput = '/tmp/' . uniqid() . '.mp4';

        if (file_exists($ffmpegPath)) {
            $command = sprintf(
                '%s -i %s -vf "scale=min(iw\\,1280):max(ih\\,720),force_original_aspect_ratio=decrease" -c:v libx264 -preset medium -crf 23 -c:a aac -b:a 128k -movflags +faststart -y %s 2>&1',
                escapeshellcmd($ffmpegPath),
                escapeshellarg($tempInput),
                escapeshellarg($tempOutput)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempOutput)) {
                copy($tempOutput, $absolutePath);
                @unlink($tempOutput);
            } else {
                copy($tempInput, $absolutePath);
            }
        } else {
            copy($tempInput, $absolutePath);
        }

        $size = filesize($absolutePath);

        // Upload to R2 if configured
        if ($this->s3Client) {
            $this->uploadToR2($absolutePath, $relativePath);
        }

        return [
            'type' => 'video',
            'filename' => basename($relativePath),
            'path' => $relativePath,
            'mime_type' => $file->getMimeType(),
            'size' => $size,
        ];
    }

    protected function uploadToR2(string $localPath, string $r2Path): bool
    {
        if (!$this->s3Client) {
            return false;
        }

        try {
            $bucket = config('filesystems.disks.r2.bucket');
            $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $r2Path,
                'SourceFile' => $localPath,
                'ContentType' => $this->getMimeType($r2Path),
                'ACL' => 'public-read',
            ]);
            \Log::info('Uploaded to R2', ['path' => $r2Path]);
            return true;
        } catch (\Exception $e) {
            \Log::error('R2 upload failed', [
                'path' => $r2Path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function getMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => 'application/octet-stream',
        };
    }
}
