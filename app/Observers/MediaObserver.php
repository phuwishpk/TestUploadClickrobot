<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaObserver
{
    public function deleting(Media $media): void
    {
        $disk = config('filesystems.default');

        // ลบไฟล์หลัก
        if ($media->path) {
            Storage::disk($disk)->delete($media->path);
            Log::info('Deleted media file', ['path' => $media->path, 'disk' => $disk]);
        }

        // ลบ thumbnail (ถ้ามี)
        if ($media->thumbnail_path) {
            Storage::disk($disk)->delete($media->thumbnail_path);
            Log::info('Deleted thumbnail file', ['path' => $media->thumbnail_path, 'disk' => $disk]);
        }
    }
}
