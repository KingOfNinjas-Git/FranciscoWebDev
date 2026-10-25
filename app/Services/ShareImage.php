<?php

namespace App\Services;

/**
 * The 1200x630 picture shown when a blog post or project page is shared (LinkedIn, WhatsApp, X...) and the page has
 * no cover of its own. Drawn with GD from the page title, then kept as a file under public/imgs/og.
 */
class ShareImage
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;
    private const DIR = 'imgs/og';

    public static function url(string $type, string $slug, string $locale): string
    {
        return route('og.image', ['locale' => $locale, 'type' => $type, 'slug' => $slug]);
    }

    public static function available(): bool
    {
        return function_exists('imagettftext') && is_file(self::font());
    }

    /** Relative path of the stored picture; the name changes with the text so edits get a fresh picture. */
    public static function path(string $type, string $slug, string $locale, string $title, string $kicker): string
    {
        return self::DIR . "/{$type}-{$slug}-{$locale}-" . substr(md5($title . '|' . $kicker . '|v1'), 0, 8) . '.png';
    }

    /** PNG bytes, or null when this server's GD has no text support. */
    public static function render(string $title, string $kicker): ?string
    {
        if (! self::available()) {
            return null;
        }

        $font = self::font();
        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($im, true);

        // Dark blue gradient, like the site
        for ($y = 0; $y < self::HEIGHT; $y++) {
            $t = $y / self::HEIGHT;
            $c = imagecolorallocate($im, (int) (2 + 8 * $t), (int) (8 + 22 * $t), (int) (23 + 45 * $t));
            imageline($im, 0, $y, self::WIDTH, $y, $c);
        }
        // Soft glows
        foreach ([[1080, 40, 760, [56, 189, 248]], [60, 640, 640, [99, 102, 241]]] as [$cx, $cy, $diameter, $rgb]) {
            $steps = 90;
            for ($i = $steps; $i >= 1; $i--) {
                // Each circle is only slightly more opaque than the one around it, so the glow looks smooth
                $alpha = 127 - (int) round(14 * (1 - $i / $steps) ** 1.6);
                imagefilledellipse($im, $cx, $cy, (int) ($diameter * $i / $steps), (int) ($diameter * $i / $steps), imagecolorallocatealpha($im, $rgb[0], $rgb[1], $rgb[2], $alpha));
            }
        }

        $white = imagecolorallocate($im, 255, 255, 255);
        $cyan = imagecolorallocate($im, 56, 189, 248);
        $muted = imagecolorallocate($im, 148, 163, 184);

        // Accent bar on the left
        imagefilledrectangle($im, 0, 0, 14, self::HEIGHT, $cyan);

        $left = 84;
        self::text($im, 22, $left, 120, $cyan, $font, mb_strtoupper($kicker), 3);

        // Title: as large as fits in at most four lines
        $maxWidth = self::WIDTH - $left * 2;
        // (sizes are in pixels; GD wants points)
        foreach ([64, 56, 48, 42] as $px) {
            $lineHeight = (int) ($px * 1.22);
            $maxLines = intdiv(300, $lineHeight);
            $lines = self::wrap($title, $px * 0.75, $font, $maxWidth);
            if (count($lines) <= $maxLines) {
                break;
            }
        }
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], " .,;:") . '...';
        }
        $y = 175 + $px;
        foreach ($lines as $line) {
            self::text($im, $px * 0.75, $left, $y, $white, $font, $line, 0, true);
            $y += $lineHeight;
        }

        // Footer: monogram + address
        $cy = self::HEIGHT - 74;
        imagefilledellipse($im, $left + 26, $cy, 52, 52, $cyan);
        $navy = imagecolorallocate($im, 2, 8, 23);
        self::text($im, 15, $left + 9, $cy + 7, $navy, $font, 'FC', 0, true);
        self::text($im, 19, $left + 70, $cy + 8, $muted, $font, 'franciscowebdev.com');

        ob_start();
        imagepng($im, null, 6);
        $png = ob_get_clean();
        imagedestroy($im);

        return $png ?: null;
    }

    private static function font(): string
    {
        return resource_path('fonts/Inter.ttf');
    }

    /** Draws text; "bold" thickens the strokes because the bundled font file is a single regular weight. */
    private static function text($im, float $size, int $x, int $y, int $color, string $font, string $text, int $spacing = 0, bool $bold = false): void
    {
        $draw = function (int $dx) use ($im, $size, $x, $y, $color, $font, $text, $spacing) {
            if ($spacing === 0) {
                imagettftext($im, $size, 0, $x + $dx, $y, $color, $font, $text);

                return;
            }
            $cursor = $x + $dx;
            foreach (mb_str_split($text) as $char) {
                imagettftext($im, $size, 0, $cursor, $y, $color, $font, $char);
                $box = imagettfbbox($size, 0, $font, $char);
                $cursor += ($box[2] - $box[0]) + $spacing;
            }
        };

        foreach ($bold ? [0, 1, 2] : [0] as $dx) {
            $draw($dx);
        }
    }

    /** @return string[] */
    private static function wrap(string $text, float $size, string $font, int $maxWidth): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $try = $line === '' ? $word : "$line $word";
            $box = imagettfbbox($size, 0, $font, $try);
            if (($box[2] - $box[0]) > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
