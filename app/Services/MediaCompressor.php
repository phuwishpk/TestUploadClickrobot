<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\School;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaCompressor
{
    protected ImageManager $imageManager;
    protected string $uploadsPath;
    protected ?S3Client $s3Client = null;
    protected ?string $r2Bucket = null;
    protected ?School $currentSchool = null;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->uploadsPath = '/var/www/html/storage/app/uploads';
    }

    /**
     * Set school for R2 bucket selection
     */
    public function setSchool(School $school): self
    {
        $this->currentSchool = $school;
        $this->r2Bucket = $school->getR2Bucket();
        $this->initS3Client();
        return $this;
    }

    /**
     * Get the S3Client for the current school bucket
     */
    protected function getS3Client(): ?S3Client
    {
        if (!$this->currentSchool) {
            $this->initS3Client();
        }
        return $this->s3Client;
    }

    protected function initS3Client(): void
    {
        if (!config('filesystems.disks.r2')) {
            return;
        }

        $bucket = $this->r2Bucket ?? config('filesystems.disks.r2.bucket');

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

    /**
     * Refresh R2 bucket from config
     */
    public function refreshBucket(): void
    {
        if ($this->currentSchool) {
            $this->r2Bucket = $this->currentSchool->getR2Bucket();
        } else {
            $this->r2Bucket = config('filesystems.disks.r2.bucket');
        }
    }

    public function compress(UploadedFile $file, Student $student, Classroom $classroom, string $uploadDate): array
    {
        $mimeType = $file->getMimeType();
        $type = $this->determineType($mimeType);

        // Set school from classroom if not already set
        if (!$this->currentSchool && $classroom->school) {
            $this->setSchool($classroom->school);
        }

        $dateObj = \Carbon\Carbon::parse($uploadDate);
        $dateStr = $dateObj->format('dmy');

        $r2Service = app(R2FolderService::class);
        $classFolder = $classroom->folder_slug;
        $studentFolder = $r2Service->getStudentFolder($classroom, $student);

        $folderPath = sprintf('%s/%s', $classFolder, $dateStr);
        $filename = $this->generateFilenameWithDate($dateStr, $file);
        $fullPath = $folderPath . '/' . $studentFolder . '/' . $filename;
        $absolutePath = $this->uploadsPath . '/' . $fullPath;

        \Log::info('MediaCompressor Start', [
            'classroom_slug' => $classFolder,
            'student_folder' => $studentFolder,
            'student_code' => $student->code,
            'folderPath' => $folderPath,
            'fullPath' => $fullPath,
            'type' => $type,
            'r2_bucket' => $this->r2Bucket,
        ]);

        if ($type === 'image') {
            return $this->compressImage($file, $absolutePath, $fullPath);
        }

        return $this->compressVideo($file, $absolutePath, $fullPath);
    }

    protected function generateFilenameWithDate(string $dateStr, UploadedFile $file): string
    {
        $timeStr = now()->format('His');
        $random = substr(md5(uniqid()), 0, 4);
        $extension = $file->getClientOriginalExtension() ?: $this->getExtensionFromMime($file->getMimeType());
        
        return "{$dateStr}_{$timeStr}_{$random}.{$extension}";
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
        
        $dir = dirname($absolutePath);
        
        if (!is_dir($dir)) {
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
        
        $image = $this->imageManager->read($tempFile);
        
        $maxWidth = 2048;
        $maxHeight = 2048;
        
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $absolutePath);
        $webpRelativePath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $relativePath);
        
        $originalSize = filesize($tempFile);
        
        if (!function_exists('imagewebp')) {
            \Log::warning('WebP not supported by GD, falling back to JPEG compression');
            $jpgPath = preg_replace('/\.(png|gif)$/i', '.jpg', $absolutePath);
            $jpgRelativePath = preg_replace('/\.(png|gif)$/i', '.jpg', $relativePath);
            
            $image->toJpeg(80)->save($jpgPath);
            $compressedSize = filesize($jpgPath);
            
            \Log::info('Image compression (JPEG fallback)', [
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'saved_bytes' => $originalSize - $compressedSize,
                'reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1)
            ]);
            
            $size = $compressedSize;
            
            $this->uploadToR2($jpgPath, $jpgRelativePath);
            
            return [
                'type' => 'image',
                'filename' => basename($jpgRelativePath),
                'path' => $jpgRelativePath,
                'mime_type' => 'image/jpeg',
                'size' => $size,
                'original_size' => $originalSize,
                'compression_saved_bytes' => $originalSize - $compressedSize,
                'compression_reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1),
            ];
        }
        
        $image->toWebp(80)->save($webpPath);
        
        if (!file_exists($webpPath)) {
            throw new \RuntimeException("Failed to create WebP file: {$webpPath}");
        }
        
        $compressedSize = filesize($webpPath);
        
        if ($compressedSize >= $originalSize) {
            \Log::info('WebP compression did not reduce size, trying JPEG');
            @unlink($webpPath);
            
            $jpgPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.jpg', $absolutePath);
            $jpgRelativePath = preg_replace('/\.(jpe?g|png|gif)$/i', '.jpg', $relativePath);
            $image->toJpeg(75)->save($jpgPath);
            $compressedSize = filesize($jpgPath);
            
            \Log::info('Image compression (JPEG)', [
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'saved_bytes' => $originalSize - $compressedSize,
                'reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1)
            ]);
            
            $size = $compressedSize;
            
            $this->uploadToR2($jpgPath, $jpgRelativePath);
            
            return [
                'type' => 'image',
                'filename' => basename($jpgRelativePath),
                'path' => $jpgRelativePath,
                'mime_type' => 'image/jpeg',
                'size' => $size,
                'original_size' => $originalSize,
                'compression_saved_bytes' => $originalSize - $compressedSize,
                'compression_reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1),
            ];
        }
        
        \Log::info('Image compression result', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'saved_bytes' => $originalSize - $compressedSize,
            'reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1)
        ]);
        
        $size = $compressedSize;

        $this->uploadToR2($webpPath, $webpRelativePath);

        return [
            'type' => 'image',
            'filename' => basename($webpRelativePath),
            'path' => $webpRelativePath,
            'mime_type' => 'image/webp',
            'size' => $size,
            'original_size' => $originalSize,
            'compression_saved_bytes' => $originalSize - $compressedSize,
            'compression_reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1),
        ];
    }

    protected function compressVideo(UploadedFile $file, string $absolutePath, string $relativePath): array
    {
        $tempInput = $file->getPathname();
        $originalSize = filesize($tempInput);
        $compressionSavedBytes = 0;
        $compressionReductionPercent = 0;
        
        $dir = dirname($absolutePath);
        
        if (!is_dir($dir)) {
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
        
        if (!file_exists($ffmpegPath)) {
            \Log::warning('FFmpeg not found at ' . $ffmpegPath . ' - video copied without compression');
            copy($tempInput, $absolutePath);
        } else {
            $tempOutput = '/tmp/' . uniqid() . '_compressed.mp4';
            
            // Get video info to check if re-encoding is needed
            $probeCmd = sprintf('%s -i %s 2>&1', $ffmpegPath, escapeshellarg($tempInput));
            exec($probeCmd, $probeOutput, $probeCode);
            $probeText = implode("\n", $probeOutput);
            
            \Log::info('FFmpeg probe result', ['probe' => substr($probeText, 0, 500)]);
            
            // Check if video needs scaling (width > 1280) or re-encoding
            $originalWidth = 1920; // default
            if (preg_match('/([0-9]{2,4})x([0-9]{2,4})/', $probeText, $matches)) {
                $originalWidth = (int)$matches[1];
            }
            
            \Log::info('Video info', ['original_width' => $originalWidth, 'probe' => substr($probeText, 0, 200)]);

            $originalBitrateKbps = 0;
            if (preg_match('/bitrate:\s*(\d+)\s*kb\/s/i', $probeText, $matches)) {
                $originalBitrateKbps = (int) $matches[1];
            }
            
            // Skip compression if original is already small enough (< 10MB)
            if ($originalSize < 10 * 1024 * 1024) {
                \Log::info('Video is already small enough, skipping compression', ['size' => $originalSize]);
                copy($tempInput, $absolutePath);
            } elseif ($originalWidth <= 1280 && $originalBitrateKbps > 0 && $originalBitrateKbps <= 1800) {
                \Log::info('Video appears already compressed, skipping compression', [
                    'size' => $originalSize,
                    'width' => $originalWidth,
                    'bitrate_kbps' => $originalBitrateKbps,
                ]);
                copy($tempInput, $absolutePath);
            } else {
                // Use 2-pass encoding for better compression with target bitrate
                $targetBitrate = '1500k'; // 1.5 Mbps - good quality for mobile
                
                if ($originalWidth > 1280) {
                    // Scale down + 2-pass encoding
                    $pass1 = sprintf(
                        '%s -i %s -vf "scale=1280:-2" -c:v libx264 -preset fast -b:v %s -pass 1 -an -f null - 2>&1',
                        $ffmpegPath, escapeshellarg($tempInput), $targetBitrate
                    );
                    $pass2 = sprintf(
                        '%s -i %s -vf "scale=1280:-2" -c:v libx264 -preset fast -b:v %s -pass 2 -c:a aac -b:a 48k -movflags +faststart -y %s 2>&1',
                        $ffmpegPath, escapeshellarg($tempInput), $targetBitrate, escapeshellarg($tempOutput)
                    );
                    
                    \Log::info('FFmpeg 2-pass compression', ['pass1' => $pass1, 'pass2' => $pass2]);
                    exec($pass1, $p1Out, $p1Code);
                    exec($pass2, $p2Out, $p2Code);
                } else {
                    // 2-pass encoding without scaling
                    $pass1 = sprintf(
                        '%s -i %s -c:v libx264 -preset fast -b:v %s -pass 1 -an -f null - 2>&1',
                        $ffmpegPath, escapeshellarg($tempInput), $targetBitrate
                    );
                    $pass2 = sprintf(
                        '%s -i %s -c:v libx264 -preset fast -b:v %s -pass 2 -c:a aac -b:a 48k -movflags +faststart -y %s 2>&1',
                        $ffmpegPath, escapeshellarg($tempInput), $targetBitrate, escapeshellarg($tempOutput)
                    );
                    
                    \Log::info('FFmpeg 2-pass compression', ['pass1' => $pass1, 'pass2' => $pass2]);
                    exec($pass1, $p1Out, $p1Code);
                    exec($pass2, $p2Out, $p2Code);
                }
                
                if (file_exists($tempOutput) && filesize($tempOutput) > 0) {
                    $compressedSize = filesize($tempOutput);
                    $attemptedSavedBytes = $originalSize - $compressedSize;
                    $attemptedReductionPercent = $originalSize > 0 ? round((1 - $compressedSize / $originalSize) * 100, 1) : 0;
                    
                    \Log::info('Video compression result', [
                        'original_size' => $originalSize,
                        'compressed_size' => $compressedSize,
                        'saved_bytes' => $attemptedSavedBytes,
                        'reduction_percent' => $attemptedReductionPercent
                    ]);
                    
                    // Only use compressed version if it's actually smaller
                    if ($compressedSize < $originalSize) {
                        $compressionSavedBytes = $attemptedSavedBytes;
                        $compressionReductionPercent = $attemptedReductionPercent;
                        copy($tempOutput, $absolutePath);
                    } else {
                        \Log::info('Compression did not reduce size, using original');
                        copy($tempInput, $absolutePath);
                    }
                    // Clean up 2-pass log files
                    @unlink($tempOutput);
                    @unlink('/tmp/ffmpeg2pass-0.log');
                    @unlink('/tmp/ffmpeg2pass-0.log.mbtree');
                } else {
                    \Log::error('FFmpeg compression failed, using original');
                    copy($tempInput, $absolutePath);
                }
            }
        }

        $size = filesize($absolutePath);

        // Extract video thumbnail
        $thumbnailResult = $this->extractVideoThumbnail($absolutePath, $relativePath);

        $this->uploadToR2($absolutePath, $relativePath);
        if ($thumbnailResult) {
            $this->uploadToR2($thumbnailResult['absolute_path'], $thumbnailResult['relative_path']);
        }

        return [
            'type' => 'video',
            'filename' => basename($relativePath),
            'path' => $relativePath,
            'mime_type' => 'video/mp4',
            'size' => $size,
            'thumbnail_path' => $thumbnailResult['relative_path'] ?? null,
            'original_size' => $originalSize,
            'compression_saved_bytes' => $compressionSavedBytes,
            'compression_reduction_percent' => $compressionReductionPercent,
        ];
    }

    public function extractVideoThumbnail(string $videoPath, string $videoRelativePath): ?array
    {
        $ffmpegPath = '/usr/bin/ffmpeg';

        if (!file_exists($ffmpegPath)) {
            \Log::warning('FFmpeg not found, cannot extract thumbnail');
            return null;
        }

        $thumbnailFilename = preg_replace('/\.[^.]+$/', '.jpg', basename($videoRelativePath));
        $thumbnailRelativePath = dirname($videoRelativePath) . '/' . $thumbnailFilename;
        $thumbnailAbsolutePath = $this->uploadsPath . '/' . $thumbnailRelativePath;

        $thumbDir = dirname($thumbnailAbsolutePath);
        if (!is_dir($thumbDir)) {
            @mkdir($thumbDir, 0755, true);
        }

        // Extract frame at 10 seconds (after opening/black frames)
        $extractCmd = sprintf(
            '%s -ss 10 -i %s -vframes 1 -q:v 1 -vf "scale=480:-2" -update 1 %s 2>&1',
            $ffmpegPath,
            escapeshellarg($videoPath),
            escapeshellarg($thumbnailAbsolutePath)
        );

        \Log::info('Extracting video thumbnail', ['cmd' => $extractCmd]);
        exec($extractCmd, $extractOutput, $extractCode);

        if (!file_exists($thumbnailAbsolutePath) || filesize($thumbnailAbsolutePath) < 5000) {
            \Log::warning('Video thumbnail too small or failed', [
                'path' => $thumbnailRelativePath,
                'size' => file_exists($thumbnailAbsolutePath) ? filesize($thumbnailAbsolutePath) : 0,
            ]);
            @unlink($thumbnailAbsolutePath);
            return null;
        }

        \Log::info('Video thumbnail extracted', [
            'thumbnail_path' => $thumbnailRelativePath,
            'size' => filesize($thumbnailAbsolutePath)
        ]);

        return [
            'absolute_path' => $thumbnailAbsolutePath,
            'relative_path' => $thumbnailRelativePath,
        ];
    }

    protected function uploadToR2(string $localPath, string $r2Path): bool
    {
        $s3Client = $this->getS3Client();
        if (!$s3Client) {
            return false;
        }

        try {
            $s3Client->putObject([
                'Bucket' => $this->r2Bucket,
                'Key' => $r2Path,
                'SourceFile' => $localPath,
                'ContentType' => $this->getMimeType($r2Path),
                'ACL' => 'public-read',
            ]);
            \Log::info('Uploaded to R2', ['bucket' => $this->r2Bucket, 'path' => $r2Path]);
            return true;
        } catch (\Exception $e) {
            \Log::error('R2 upload failed', [
                'bucket' => $this->r2Bucket,
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
