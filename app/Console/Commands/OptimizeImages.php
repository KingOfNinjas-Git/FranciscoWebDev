<?php

namespace App\Console\Commands;

use App\Models\ProjectImage;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : Show what would change without touching files}';

    protected $description = 'Convert existing project images to resized WebP files';

    public function handle(ImageOptimizer $optimizer): int
    {
        if (!$optimizer->supported()) {
            $this->error('PHP GD with WebP support is not available on this server.');
            return self::FAILURE;
        }

        $saved = 0;
        $converted = 0;

        foreach (ProjectImage::all() as $image) {
            $relative = $image->public_path;
            $source = public_path($relative);

            if (!is_file($source) || str_ends_with(strtolower($relative), '.webp')) continue;

            $newRelative = preg_replace('/\.[a-z0-9]+$/i', '', $relative) . '.webp';
            $dest = public_path($newRelative);

            if ($this->option('dry-run')) {
                $this->line("would convert {$relative}");
                continue;
            }

            if (!$optimizer->convert($source, $dest)) {
                $this->warn("skipped {$relative}");
                continue;
            }

            // Only switch over if the WebP is actually smaller
            if (filesize($dest) >= filesize($source)) {
                unlink($dest);
                $this->line("kept original {$relative} (already small)");
                continue;
            }

            $saved += filesize($source) - filesize($dest);
            unlink($source);
            $image->update(['path' => $newRelative]);
            $converted++;
            $this->info("converted {$relative} -> {$newRelative}");
        }

        $this->info("Done: {$converted} converted, " . round($saved / 1048576, 1) . ' MB saved.');

        return self::SUCCESS;
    }
}
