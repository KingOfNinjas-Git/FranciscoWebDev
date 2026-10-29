<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    public const TRANSLATABLE = ['title', 'excerpt', 'body'];
    public const TRANSLATION_LOCALES = ['pt', 'es'];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'tags', 'cover_path', 'cover_alt',
        'status', 'published_at', 'translations', 'ai_assisted',
    ];

    protected $casts = [
        'tags' => 'array',
        'translations' => 'array',
        'published_at' => 'datetime',
        'ai_assisted' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->lte(now());
    }

    /**
     * Text for the given locale, falling back to the English base column.
     */
    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $translated = $this->translations[$locale][$field] ?? null;

        return filled($translated) ? $translated : $this->{$field};
    }

    public function hasTranslation(string $locale): bool
    {
        return filled($this->translations[$locale]['body'] ?? null);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? asset($this->cover_path) : null;
    }

    /** Responsive "srcset" of the cover (null when there are no smaller versions). */
    public function coverSrcset(): ?string
    {
        return $this->cover_path ? \App\Services\ImageVariants::srcset($this->cover_path) : null;
    }

    /** @return array{0: int, 1: int}|null width and height of the cover file */
    public function coverSize(): ?array
    {
        return $this->cover_path ? \App\Services\ImageVariants::size($this->cover_path) : null;
    }

    public function readingMinutes(?string $locale = null): int
    {
        $words = preg_match_all('/[\p{L}\p{N}\']+/u', strip_tags((string) $this->localized('body', $locale)));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Markdown -> safe HTML. Raw HTML in the source is stripped, so neither the admin nor the AI can inject scripts.
     */
    public static function renderMarkdown(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // External links open in a new tab without leaking the opener
        return preg_replace('/<a href="(https?:\/\/[^"]+)"/', '<a href="$1" target="_blank" rel="noopener noreferrer"', $html);
    }

    public function html(?string $locale = null): string
    {
        return self::renderMarkdown((string) $this->localized('body', $locale));
    }

    /**
     * The article body with an id and a "#" link on every h2/h3, plus those headings as a list (for the table of contents).
     *
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    public function article(?string $locale = null): array
    {
        $toc = [];
        $used = [];
        $html = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/su', function ($m) use (&$toc, &$used) {
            $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $base = Str::slug($text) ?: 'section';
            $id = $base;
            for ($i = 2; isset($used[$id]); $i++) {
                $id = $base . '-' . $i;
            }
            $used[$id] = true;
            $toc[] = ['id' => $id, 'text' => $text, 'level' => (int) $m[1]];

            return '<h' . $m[1] . ' id="' . e($id) . '">' . $m[2]
                . '<a class="heading-anchor" href="#' . e($id) . '" aria-label="' . e(__('site.blog.anchor', ['section' => $text])) . '">#</a></h' . $m[1] . '>';
        }, $this->html($locale));

        return ['html' => $html, 'toc' => $toc];
    }
}
