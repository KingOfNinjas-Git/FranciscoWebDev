<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PortfolioProjects;
use Illuminate\Support\Str;

/**
 * The public page of one project (a small case study), in the language of the URL.
 */
class ProjectController extends Controller
{
    public function show(PortfolioProjects $portfolio, string $slug, ?string $locale = null)
    {
        $all = $portfolio->all();
        $index = $all->search(fn ($p) => $p->slug === $slug);

        abort_if($index === false, 404);

        $project = $all[$index];

        return view('projects.show', [
            'project' => $project,
            'lead' => Str::limit(trim($project->description) ?: $project->title, 160),
            'notesHtml' => filled($project->notes) ? Post::renderMarkdown($project->notes) : null,
            'previous' => $all[$index - 1] ?? null,
            'next' => $all[$index + 1] ?? null,
            'related' => $this->relatedPosts($project),
        ]);
    }

    /** Up to three blog posts that share a tag with the project's technologies. */
    private function relatedPosts(object $project)
    {
        $tags = array_map('mb_strtolower', $project->tags);
        if (! $tags) {
            return collect();
        }

        try {
            return Post::published()->latest('published_at')->limit(40)->get()
                ->filter(fn (Post $post) => count(array_intersect($tags, array_map('mb_strtolower', $post->tags ?? []))) > 0)
                ->take(3)
                ->values();
        } catch (\Throwable $e) {
            return collect(); // blog tables not created yet
        }
    }
}
