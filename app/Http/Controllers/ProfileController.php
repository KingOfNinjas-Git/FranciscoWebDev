<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function projects()
    {
        // 1. Define your static projects here (fallback data)
        $rawProjects = collect([
            (object)[
                'title' => 'MAE',
                'image' => 'imgs/maehome.jpg',
                'description' => 'MAE — a responsive, accessible website showcasing services and contact features. Built with semantic HTML, Tailwind CSS and Laravel Blade.',
                'tags' => ['Laravel','Tailwind','Accessibility'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
            (object)[
                'title' => 'GameHaven',
                'image' => 'imgs/Gamehaven.png',
                'description' => 'GameHaven — a game catalogue and community landing page with interactive UI elements and launch promos.',
                'tags' => ['HTML','CSS','JavaScript'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
            (object)[
                'title' => 'Blog Page',
                'image' => 'imgs/Bloghome.png',
                'description' => 'Blog Page — a clean blog layout with article lists, tags, and accessible reading experience. Perfect for technical writing and tutorials.',
                'tags' => ['Laravel','Markdown','Responsive'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
        ]);

        // Try to load from DB
        try {
            $dbProjects = Project::with('images')->get();
            if ($dbProjects->isNotEmpty()) {
                $rawProjects = $dbProjects;
            }
        } catch (\Exception $e) {
            // Fallback to static if DB fails
        }

        // 2. Normalize the data for the view
        $projects = $rawProjects->map(function($project) {
            // Ensure tags is an array
            $tags = [];
            if (isset($project->technologies)) {
                $tags = $project->technologies;
            } elseif (isset($project->tags)) {
                $tags = $project->tags;
            }
            
            if (is_string($tags)) {
                $tags = explode(',', $tags);
            }
            if (!is_array($tags)) $tags = [];

            // Ensure image URL is resolved
            $imgUrl = null;
            
            // Priority 1: DB Relation images
            if (isset($project->images) && is_iterable($project->images) && count($project->images) > 0) {
                $first = $project->images->first();
                $imgUrl = $first->path ?? null;
            }
            
            // Priority 2: Direct image property (static data)
            if (!$imgUrl) {
                $imgUrl = $project->image ?? null;
            }

            // Finalize URL
            if ($imgUrl) {
                 $imgUrl = asset($imgUrl);
            } else {
                 $imgUrl = 'https://via.placeholder.com/800x450?text=' . urlencode($project->title ?? 'Project');
            }

            // Return a clean object for the view
            return (object)[
                'title' => $project->title ?? 'Untitled',
                'description' => $project->description ?? '',
                'tags' => $tags,
                'image_url' => $imgUrl,
                'live_url' => $project->live_url ?? '#',
                'repo_url' => $project->repo_url ?? '#',
            ];
        });

        return view('projects', ['projects' => $projects]);
    }

    public function projectShow($slug)
    {
        // Try DB first
        try {
            $project = Project::where('slug', $slug)->with('images')->first();
            if ($project) {
                return view('projects.show', ['project' => $project]);
            }
        } catch (\Exception $e) {
            // Fall back to local data below
        }

        // Fallback: recreate the local projects array used in the view
        $local = [
            (object)[
                'title' => 'MAE',
                'image' => 'imgs/maehome.jpg',
                'description' => 'MAE — a responsive, accessible website showcasing services and contact features. Built with semantic HTML, Tailwind CSS and Laravel Blade.',
                'tags' => ['Laravel','Tailwind','Accessibility'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
            (object)[
                'title' => 'GameHaven',
                'image' => 'imgs/Gamehaven.png',
                'description' => 'GameHaven — a game catalogue and community landing page with interactive UI elements and launch promos.',
                'tags' => ['HTML','CSS','JavaScript'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
            (object)[
                'title' => 'Blog Page',
                'image' => 'imgs/Bloghome.png',
                'description' => 'Blog Page — a clean blog layout with article lists, tags, and accessible reading experience. Perfect for technical writing and tutorials.',
                'tags' => ['Laravel','Markdown','Responsive'],
                'live_url' => '#',
                'repo_url' => '#'
            ],
        ];

        foreach ($local as $p) {
            if (Str::slug($p->title) === $slug) {
                // Normalize to an object that the view expects
                $p->images = [];
                return view('projects.show', ['project' => $p]);
            }
        }

        abort(404);
    }
}