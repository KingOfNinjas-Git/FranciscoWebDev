<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index(Request $request, ?string $locale = null)
    {
        if ($locale === null) {
            // Returning visitors who picked Portuguese/Spanish land on that version of "/"
            $saved = $request->cookie('site_locale');
            if (in_array($saved, ['pt', 'es'], true)) {
                return redirect()->to(SetLocale::homeUrl($saved));
            }
        } else {
            // Arriving on /pt or /es (e.g. from a shared link) also remembers the choice
            Cookie::queue('site_locale', $locale, 60 * 24 * 365);
        }

        return view('home', ['projects' => $this->projects()]);
    }

    public function sitemap()
    {
        return response()
            ->view('sitemap', ['locales' => SetLocale::LOCALES, 'lastmod' => now()->toDateString()])
            ->header('Content-Type', 'application/xml');
    }

    private function projects()
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
                        'alt' => $img->localized('alt') ?: $title,
                        'caption' => $img->localized('caption'),
                    ];
                }
            } elseif (!empty($project->image)) {
                $images[] = ['url' => asset($project->image), 'alt' => $title, 'caption' => null];
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
                'year' => !empty($project->created_at) && is_object($project->created_at) ? $project->created_at->format('Y') : null,
                'live_url' => $valid($project->live_url ?? null),
                'repo_url' => $valid($project->repo_url ?? null),
            ];
        })->values();
    }
}
