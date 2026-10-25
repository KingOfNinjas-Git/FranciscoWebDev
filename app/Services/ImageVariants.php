<?php

namespace App\Services;

/**
 * Smaller copies of an uploaded picture (480 and 960 px wide) so phones and small cards don't download the
 * full-size file. Variants sit next to the original: photo.webp -> photo-480.webp, photo-960.webp.
 */
class ImageVariants
{
    public const WIDTHS = [480, 960];
    private const QUALITY = 80;

    /** @var array<string, array|null> image sizes already read in this request */
    private static array $sizes = [];

    /** "imgs/blog/a.webp" -> "imgs/blog/a-480.webp" */
    public static function variantPath(string $relative, int $width): string
    {
        return preg_replace('/\.[a-z0-9]+$/i', '', $relative) . "-{$width}.webp";
    }

    /**
     * Writes the variants that don't exist yet and are smaller than the original.
     *
     * @return string[] relative paths of the files created
     */
    public static function make(string $relative): array
    {
        $source = public_path($relative);
        $info = is_file($source) ? @getimagesize($source) : false;

        if (! $info || ! function_exists('imagewebp') || ($info[0] * $info[1]) > 40_000_000) {
            return [];
        }

        $created = [];
        $image = null;

        foreach (self::WIDTHS as $width) {
            $target = self::variantPath($relative, $width);
            if ($info[0] <= $width || is_file(public_path($target))) {
                continue; // original is already that small, or the variant exists
            }

            $image ??= @imagecreatefromstring((string) file_get_contents($source));
            if (! $image) {
                break;
            }

            $scaled = imagescale($image, $width, -1, IMG_BICUBIC);
            if (! $scaled) {
                continue;
            }

            imagepalettetotruecolor($scaled);
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);

            if (imagewebp($scaled, public_path($target), self::QUALITY) && is_file(public_path($target))) {
                $created[] = $target;
            }
            imagedestroy($scaled);
        }

        if ($image) {
            imagedestroy($image);
        }

        return $created;
    }

    /** True when a wider original is missing one of its smaller versions. */
    public static function missing(string $relative): bool
    {
        $size = self::size($relative);
        if (! $size) {
            return false;
        }

        foreach (self::WIDTHS as $width) {
            if ($size[0] > $width && ! is_file(public_path(self::variantPath($relative, $width)))) {
                return true;
            }
        }

        return false;
    }

    /** @return array{0: int, 1: int}|null width and height of the original */
    public static function size(string $relative): ?array
    {
        if (! array_key_exists($relative, self::$sizes)) {
            $file = public_path($relative);
            $info = is_file($file) ? @getimagesize($file) : false;
            self::$sizes[$relative] = $info ? [(int) $info[0], (int) $info[1]] : null;
        }

        return self::$sizes[$relative];
    }

    /** "…-480.webp 480w, …-960.webp 960w, original 1600w", or null if there are no variants. */
    public static function srcset(string $relative): ?string
    {
        $size = self::size($relative);
        if (! $size) {
            return null;
        }

        $parts = [];
        foreach (self::WIDTHS as $width) {
            $variant = self::variantPath($relative, $width);
            if ($size[0] > $width && is_file(public_path($variant))) {
                $parts[] = asset($variant) . " {$width}w";
            }
        }

        if (! $parts) {
            return null;
        }

        $parts[] = asset($relative) . " {$size[0]}w";

        return implode(', ', $parts);
    }

    /** The smallest version (for thumbnails and blurred backgrounds), or the original if there is none. */
    public static function thumb(string $relative): string
    {
        $variant = self::variantPath($relative, self::WIDTHS[0]);

        return is_file(public_path($variant)) ? asset($variant) : asset($relative);
    }

    /** Removes the variants of a picture (call when the picture itself is deleted). */
    public static function delete(string $relative): void
    {
        foreach (self::WIDTHS as $width) {
            $file = public_path(self::variantPath($relative, $width));
            if (is_file($file)) {
                @unlink($file);
            }
        }
        unset(self::$sizes[$relative]);
    }
}
