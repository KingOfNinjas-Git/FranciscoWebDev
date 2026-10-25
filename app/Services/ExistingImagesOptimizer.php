<?php

namespace App\Services;

use App\Models\Post;
use App\Models\ProjectImage;
use Illuminate\Support\Facades\Cache;

/**
 * Converts images that were uploaded before optimisation existed (large PNG/JPG) into resized WebP files,
 * and points the database at the new files. Used by the admin button and by `php artisan images:optimize`.
 */
class ExistingImagesOptimizer
{
    // Files under this size are not worth converting
    private const MIN_BYTES = 100 * 1024;
    private const SKIP_KEY = 'images.optimizer.skip';

    public function __construct(private ImageOptimizer $optimizer)
    {
    }

    /**
     * Images that would be converted: [count, bytes].
     *
     * @return array{count: int, bytes: int}
     */
    public function pending(): array
    {
        $count = 0;
        $bytes = 0;
        foreach (array_keys($this->references()) as $relative) {
            if ($this->needsWork($relative)) {
                $count++;
                $bytes += filesize(public_path($relative));
            }
        }

        // Pictures that are already small files but still lack their phone-size versions
        $variants = 0;
        foreach (array_keys($this->references()) as $relative) {
            if (! $this->needsWork($relative) && ImageVariants::missing($relative)) {
                $variants++;
            }
        }

        return ['count' => $count, 'bytes' => $bytes, 'variants' => $variants];
    }

    /**
     * @return array{converted: int, kept: int, skipped: int, saved_bytes: int, log: string[]}
     */
    public function run(bool $dryRun = false): array
    {
        @set_time_limit(300);

        $result = ['converted' => 0, 'kept' => 0, 'skipped' => 0, 'saved_bytes' => 0, 'variants' => 0, 'log' => []];

        foreach ($this->references() as $relative => $owners) {
            if (!$this->needsWork($relative)) continue;
            $source = public_path($relative);

            $newRelative = preg_replace('/\.[a-z0-9]+$/i', '', $relative) . '.webp';
            $dest = public_path($newRelative);

            if ($dryRun) {
                $result['log'][] = "would convert {$relative}";
                continue;
            }

            if (!$this->optimizer->convert($source, $dest)) {
                $result['skipped']++;
                $result['log'][] = "skipped {$relative}";
                $this->remember($relative, $source);
                continue;
            }

            // Only switch over if the WebP is actually smaller
            if (filesize($dest) >= filesize($source)) {
                unlink($dest);
                $result['kept']++;
                $result['log'][] = "kept {$relative} (already small)";
                $this->remember($relative, $source);
                continue;
            }

            $result['saved_bytes'] += filesize($source) - filesize($dest);

            // Every row that used the old file now uses the new one
            foreach ($owners as $row) {
                $row->update([$row instanceof Post ? 'cover_path' : 'path' => $newRelative]);
            }
            unlink($source);

            $result['converted']++;
            $result['log'][] = "converted {$relative} -> {$newRelative}";
        }

        // Every referenced picture (now mostly WebP) gets its 480 / 960 px versions
        foreach (array_keys($this->references()) as $relative) {
            if (! ImageVariants::missing($relative)) continue;

            if ($dryRun) {
                $result['log'][] = "would make smaller versions of {$relative}";
                continue;
            }

            if (ImageVariants::make($relative)) {
                $result['variants']++;
                $result['log'][] = "made smaller versions of {$relative}";
            }
        }

        return $result;
    }

    /** @return array<string, array> normalised public path => rows that reference it */
    private function references(): array
    {
        $map = [];

        foreach (ProjectImage::all() as $image) {
            $map[$image->public_path][] = $image;
        }
        foreach (Post::whereNotNull('cover_path')->get() as $post) {
            $map[$post->cover_path][] = $post;
        }

        return $map;
    }

    private function needsWork(string $relative): bool
    {
        if (!$this->optimizer->supported() || preg_match('/\.(png|jpe?g)$/i', $relative) !== 1) {
            return false;
        }

        $file = public_path($relative);
        if (!is_file($file) || filesize($file) < self::MIN_BYTES) {
            return false;
        }

        // A file that was tried and could not be made smaller is not offered again (unless it changes)
        return (Cache::get(self::SKIP_KEY, [])[$relative] ?? null) !== filesize($file);
    }

    private function remember(string $relative, string $file): void
    {
        $skip = Cache::get(self::SKIP_KEY, []);
        $skip[$relative] = filesize($file);
        Cache::forever(self::SKIP_KEY, $skip);
    }
}
