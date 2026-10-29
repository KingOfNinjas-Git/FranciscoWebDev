<?php

namespace App\Services;

use App\Models\Project;
use App\Services\ImageVariants;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The project list shown on the public site, in the current locale.
 * Used by the home page and by the AI context reader.
 */
class PortfolioProjects
{
    public function all(): Collection
    {
        // Static fallback used when the database has no projects yet
        $rawProjects = collect([
            (object)[
                'title' => 'MAE',
                'image' => 'imgs/maehome.jpg',
                'description' => __('projects.mae'),
                'tags' => ['Laravel', 'Tailwind', 'Accessibility'],
            ],
            (object)[
                'title' => 'GameHaven',
                'image' => 'imgs/Gamehaven.png',
                'description' => __('projects.gamehaven'),
                'tags' => ['HTML', 'CSS', 'JavaScript'],
            ],
            (object)[
                'title' => 'Blog Page',
                'image' => 'imgs/Bloghome.png',
                'description' => __('projects.blog'),
                'tags' => ['Laravel', 'Markdown', 'Responsive'],
            ],
        ]);

        try {
            $dbProjects = Project::with('images')->get();
            if ($dbProjects->isNotEmpty()) {
                $rawProjects = $dbProjects;
            }
        } catch (\Exception $e) {
            // Keep the static fallback if the database is unavailable
        }

        return $rawProjects->map(function ($project) {
            $tags = $project->technologies ?? $project->tags ?? [];
            if (is_string($tags)) $tags = explode(',', $tags);
            if (!is_array($tags)) $tags = [];

            $title = $project->title ?? 'Untitled';

            $images = [];
            if (isset($project->images) && count($project->images) > 0) {
                foreach ($project->images as $img) {
                    $images[] = [
                        'url' => asset($img->public_path),
                        'path' => $img->public_path,
                        'thumb' => ImageVariants::thumb($img->public_path),
                        'srcset' => ImageVariants::srcset($img->public_path),
                        'alt' => $img->localized('alt') ?: $title,
                        'caption' => $img->localized('caption'),
                    ];
                }
            } elseif (!empty($project->image)) {
                $images[] = ['url' => asset($project->image), 'path' => $project->image, 'thumb' => ImageVariants::thumb($project->image), 'srcset' => ImageVariants::srcset($project->image), 'alt' => $title, 'caption' => null];
            }

            $valid = fn ($url) => (!empty($url) && $url !== '#') ? $url : null;
            $isModel = $project instanceof Project;

            return (object)[
                'slug' => $project->slug ?? Str::slug($title),
                'title' => ($isModel ? $project->localized('title') : $title) ?: 'Untitled',
                'description' => ($isModel ? $project->localized('description') : $project->description) ?? '',
                'notes' => $isModel ? $project->localized('notes') : null,
                'tags' => array_values(array_map('trim', $tags)),
                'images' => $images,
                'cover' => $images[0]['url'] ?? null,
                'cover_srcset' => $images[0]['srcset'] ?? null,
                'year' => !empty($project->created_at) && is_object($project->created_at) ? $project->created_at->format('Y') : null,
                'created' => !empty($project->created_at) && is_object($project->created_at) ? $project->created_at->toDateString() : null,
                'updated' => !empty($project->updated_at) && is_object($project->updated_at) ? $project->updated_at->toDateString() : null,
                'live_url' => $valid($project->live_url ?? null),
                'repo_url' => $valid($project->repo_url ?? null),
                // The demo video as a list of sources, MP4 first (it plays everywhere), then WebM
                'videos' => $isModel ? collect([$project->demo_video, $project->demo_video_fallback])->filter()
                    ->map(fn ($path) => ['url' => asset($path), 'type' => str_ends_with($path, '.webm') ? 'video/webm' : 'video/mp4'])
                    ->sortBy(fn ($v) => $v['type'] === 'video/mp4' ? 0 : 1)->values()->all() : [],
                'video_text' => $isModel ? $project->localized('video_description') : null,
            ];
        })->values();
    }
}
