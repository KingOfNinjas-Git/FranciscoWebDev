<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    // Fields that can be translated; the base columns hold the English text
    public const TRANSLATABLE = ['title', 'description', 'notes', 'video_description'];
    public const TRANSLATION_LOCALES = ['pt', 'es'];

    protected $fillable = [
        'title',
        'description',
        'technologies',
        'slug',
        'live_url',
        'repo_url',
        'demo_video',
        'demo_video_fallback',
        'video_description',
        'notes',
        'translations',
    ];

    protected $casts = [
        'technologies' => 'array',
        'translations' => 'array',
    ];

    public function getProjects()
    {
        return self::all();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('position');
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
        return filled($this->translations[$locale]['description'] ?? null);
    }
}
