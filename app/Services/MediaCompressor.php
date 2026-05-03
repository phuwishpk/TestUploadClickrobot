<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaCompressor
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
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

        if ($type === 'image') {
            return $this->compressImage($file, $fullPath, $mimeType);
        }

        return $this->compressVideo($file, $fullPath, $mimeType);
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

    protected function compressImage(UploadedFile $file, string $path, string $mimeType): array
    {
        $tempFile = $file->getPathname();
        
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
        
        $tempOutput = storage_path('app/temp/' . uniqid() . '.jpg');
        
        if (!is_dir(dirname($tempOutput))) {
            mkdir(dirname($tempOutput), 0755, true);
        }

        $image->toJpeg($quality)->save($tempOutput);

        $disk = config('filesystems.default', 'uploads');
        Storage::disk($disk)->put($path, file_get_contents($tempOutput));

        unlink($tempOutput);

        $size = Storage::disk($disk)->size($path);

        return [
            'type' => 'image',
            'filename' => basename($path),
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => $size,
        ];
    }

    protected function compressVideo(UploadedFile $file, string $path, string $mimeType): array
    {
        $tempInput = $file->getPathname();
        $tempOutput = storage_path('app/temp/' . uniqid() . '.mp4');
        
        if (!is_dir(dirname($tempOutput))) {
            mkdir(dirname($tempOutput), 0755, true);
        }

        $ffmpegPath = '/usr/bin/ffmpeg';
        $ffprobePath = '/usr/bin/ffprobe';

        if (!file_exists($ffmpegPath)) {
            $file->storeAs('temp', basename($path), 'local');
            
            return [
                'type' => 'video',
                'filename' => basename($path),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        $command = sprintf(
            '%s -i %s -vf "scale=min(iw\\,1280):max(ih\\,720),force_original_aspect_ratio=decrease" -c:v libx264 -preset medium -crf 23 -c:a aac -b:a 128k -movflags +faststart -y %s 2>&1',
            escapeshellcmd($ffmpegPath),
            escapeshellarg($tempInput),
            escapeshellarg($tempOutput)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($tempOutput)) {
            $disk = config('filesystems.default', 'uploads');
            Storage::disk($disk)->put($path, file_get_contents($tempOutput));
            unlink($tempOutput);
            $size = Storage::disk($disk)->size($path);

            return [
                'type' => 'video',
                'filename' => basename($path),
                'path' => $path,
                'mime_type' => 'video/mp4',
                'size' => $size,
            ];
        }

        $file->storeAs('temp', basename($path), 'local');

        return [
            'type' => 'video',
            'filename' => basename($path),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
