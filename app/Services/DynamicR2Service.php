<?php

namespace App\Services;

use App\Models\School;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

class DynamicR2Service
{
    private ?S3Client $client = null;
    private ?string $bucket = null;

    public function __construct(?School $school = null)
    {
        if ($school) {
            $this->bucket = $school->getR2Bucket();
        } else {
            $this->bucket = config('filesystems.disks.r2.bucket');
        }

        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.r2.region', 'auto'),
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.r2.key'),
                'secret' => config('filesystems.disks.r2.secret'),
            ],
        ]);
    }

    public function upload(string $localPath, string $r2Path): bool
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $r2Path,
                'SourceFile' => $localPath,
                'ContentType' => $this->getMimeType($r2Path),
            ]);

            Log::info('R2 Uploaded', [
                'bucket' => $this->bucket,
                'path' => $r2Path,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('R2 upload failed', [
                'bucket' => $this->bucket,
                'path' => $r2Path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function download(string $r2Path, string $localPath): bool
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $r2Path,
                'SaveAs' => $localPath,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('R2 download failed', [
                'bucket' => $this->bucket,
                'path' => $r2Path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function delete(string $r2Path): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $r2Path,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('R2 delete failed', [
                'bucket' => $this->bucket,
                'path' => $r2Path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function exists(string $r2Path): bool
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $r2Path,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUrl(string $r2Path): string
    {
        $baseUrl = config('filesystems.disks.r2.url');

        if ($baseUrl) {
            return rtrim($baseUrl, '/') . '/' . ltrim($r2Path, '/');
        }

        return "https://{$this->bucket}.r2.cloudflarestorage.com/{$r2Path}";
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function setBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }

    public function getClient(): S3Client
    {
        return $this->client;
    }

    private function getMimeType(string $path): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
        ];

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}
