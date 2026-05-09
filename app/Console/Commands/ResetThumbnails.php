<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;

class ResetThumbnails extends Command
{
    protected $signature = 'media:reset-thumbnails';

    protected $description = 'Reset thumbnail_path for all video media';

    public function handle(): int
    {
        $count = Media::where('type', 'video')->update(['thumbnail_path' => null]);
        $this->info("Reset {$count} video records.");
        return Command::SUCCESS;
    }
}
