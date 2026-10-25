<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Shrinks uploaded screenshots: caps the width and re-encodes as WebP.
 * Falls back to keeping the original file when GD/WebP isn't available or the image is unusual.
 */
class ImageOptimizer
{
    public const MAX_WIDTH = 1600;
    public const QUALITY = 82;
    private const MAX_PIXELS = 40_000_000; // avoid exhausting memory on huge images

    public function supported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    /**
     * Saves an upload into $dir and returns the stored file name.
     */
    public function store(UploadedFile $file, string $dir): string
    {
        $base = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $mime = (string) $file->getMimeType();

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || !$this->supported()) {
            $name = $base . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            return $name;
        }

        $name = $base . '.webp';
        if ($this->convert($file->getRealPath(), $dir . DIRECTORY_SEPARATOR . $name)) {
            return $name;
        }

        $name = $base . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $name);
        return $name;
    }

    /**
     * Converts $source to a resized WebP at $dest. Returns false (and writes nothing) on any problem.
     */
    public function convert(string $source, string $dest): bool
    {
        if (!$this->supported()) return false;

        $info = @getimagesize($source);
        if (!$info || ($info[0] * $info[1]) > self::MAX_PIXELS) return false;

        $data = @file_get_contents($source);
        $img = $data === false ? false : @imagecreatefromstring($data);
        if (!$img) return false;

        // Honour phone-camera rotation stored in EXIF
        if (($info['mime'] ?? '') === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($source)['Orientation'] ?? 1;
            $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
            if ($angle && ($rotated = imagerotate($img, $angle, 0))) {
                $img = $rotated;
            }
        }

        if (imagesx($img) > self::MAX_WIDTH) {
            $scaled = ImageVariants::resize($img, self::MAX_WIDTH);
            if ($scaled) $img = $scaled;
        }

        imagepalettetotruecolor($img);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $ok = imagewebp($img, $dest, self::QUALITY);
        imagedestroy($img);

        if (!$ok || !is_file($dest) || filesize($dest) === 0) {
            @unlink($dest);
            return false;
        }

        return true;
    }
}
