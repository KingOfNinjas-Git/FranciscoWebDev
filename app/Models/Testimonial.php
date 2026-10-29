<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    public const TRANSLATION_LOCALES = ['pt', 'es'];

    protected $fillable = ['name', 'role', 'quote', 'translations', 'source_url', 'published', 'position'];

    protected $casts = [
        'translations' => 'array',
        'published' => 'boolean',
    ];

    /** The ones shown on the site, in the order chosen. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('position')->orderBy('id');
    }

    /** The quote in the visitor's language, falling back to the original. */
    public function localizedQuote(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translated = $this->translations[$locale]['quote'] ?? null;

        return filled($translated) ? $translated : $this->quote;
    }
}
