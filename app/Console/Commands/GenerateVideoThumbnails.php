<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaCompressor;
use Illuminate\Console\Command;

class GenerateVideoThumbnails extends Command
{
    protected $signature = 'media:generate-thumbnails {--media-id=}';

    protected $description = 'Generate thumbnails for existing video media files';

    public function handle(MediaCompressor $compressor): int
    {
        $mediaId = $this->option('media-id');

        if ($mediaId) {
            $query = Media::where('id', $mediaId);
        } else {
            $query = Media::where('type', 'video')
                          ->whereNull('thumbnail_path');
        }

        $videos = $query->get();

        if ($videos->isEmpty()) {
            $this->info('No videos without thumbnails found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$videos->count()} videos without thumbnails.");

        $bar = $this->output->createProgressBar($videos->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($videos as $video) {
            try {
                $videoPath = storage_path('app/uploads/' . $video->path);

                if (!file_exists($videoPath)) {
                    $this->warn("\nVideo file not found: {$video->path}");
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $thumbnailResult = $compressor->extractVideoThumbnail($videoPath, $video->path);

                if ($thumbnailResult && isset($thumbnailResult['relative_path'])) {
                    $video->update(['thumbnail_path' => $thumbnailResult['relative_path']]);
                    $success++;
                    $this->info("\n✓ Generated thumbnail for: {$video->original_name}");
                } else {
                    $failed++;
                    $this->warn("\n✗ Failed to extract thumbnail for: {$video->original_name}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nError: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Done! Success: {$success}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
