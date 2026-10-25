<?php

namespace App\Console\Commands;

use App\Services\ExistingImagesOptimizer;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : Show what would change without touching files}';

    protected $description = 'Convert existing project and blog images to resized WebP files';

    public function handle(ExistingImagesOptimizer $optimizer, ImageOptimizer $images): int
    {
        if (!$images->supported()) {
            $this->error('PHP GD with WebP support is not available on this server.');
            return self::FAILURE;
        }

        $result = $optimizer->run((bool) $this->option('dry-run'));

        foreach ($result['log'] as $line) {
            $this->line($line);
        }

        $this->info("Done: {$result['converted']} converted, " . round($result['saved_bytes'] / 1048576, 1) . ' MB saved.');

        return self::SUCCESS;
    }
}
