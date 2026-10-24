<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProfileController extends Controller
{
    public function index()
    {
        return view('home', ['projects' => $this->projects()]);
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

            $images = [];
            if (isset($project->images) && count($project->images) > 0) {
                foreach ($project->images as $img) {
                    $images[] = ['url' => asset($img->public_path), 'alt' => $img->alt ?: $project->title];
                }
            } elseif (!empty($project->image)) {
                $images[] = ['url' => asset($project->image), 'alt' => $project->title];
            }

            $valid = fn ($url) => (!empty($url) && $url !== '#') ? $url : null;

            $isModel = $project instanceof Project;

            return (object)[
                'title' => ($isModel ? $project->localized('title') : $project->title) ?: 'Untitled',
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
