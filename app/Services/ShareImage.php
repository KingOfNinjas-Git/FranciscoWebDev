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
        return self::DIR . "/{$type}-{$slug}-{$locale}-" . substr(md5($title . '|' . $kicker . '|v2'), 0, 8) . '.png';
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

        // River green, like the site
        imagefilledrectangle($im, 0, 0, self::WIDTH, self::HEIGHT, imagecolorallocate($im, 14, 90, 82));

        $white = imagecolorallocate($im, 255, 255, 255);
        $red = imagecolorallocate($im, 196, 64, 28);
        $mint = imagecolorallocate($im, 191, 224, 217);

        // Red accent bar on the left
        imagefilledrectangle($im, 0, 0, 14, self::HEIGHT, $red);

        $left = 84;
        self::text($im, 22, $left, 120, $mint, $font, mb_strtoupper($kicker), 3);

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
        imagefilledellipse($im, $left + 26, $cy, 52, 52, $red);
        self::text($im, 15, $left + 9, $cy + 7, $white, $font, 'FC', 0, true);
        self::text($im, 19, $left + 70, $cy + 8, $mint, $font, 'franciscowebdev.com');

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
