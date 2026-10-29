<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use App\Services\PortfolioProjects;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request, ?string $locale = null)
    {
        $tag = $request->query('tag');
        $search = trim((string) $request->query('q', ''));
        $search = mb_substr($search, 0, 80);

        $query = Post::published()->latest('published_at');
        if ($tag) {
            $query->whereJsonContains('tags', (string) $tag);
        }
        if (mb_strlen($search) >= 2) {
            // Search the English text and the PT/ES translations (stored together as JSON text).
            // "!" is the LIKE escape character, so a typed % or _ matches itself instead of acting as a wildcard.
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                foreach (['title', 'excerpt', 'body', 'translations'] as $column) {
                    $q->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        } else {
            $search = '';
        }

        $posts = $query->simplePaginate(9)->withQueryString();

        $tagCounts = Post::published()->pluck('tags')->flatten()->filter()->countBy()->sortDesc()->take(24);
        $tags = $tagCounts->keys();

        // On the first page of the plain list the newest post gets the big card
        $featured = null;
        $items = $posts->getCollection();
        if ($posts->currentPage() === 1 && ! $tag && $search === '' && $items->count() > 1) {
            $featured = $items->first();
            $items = $items->slice(1)->values();
        }

        return view('blog.index', [
            'posts' => $posts, 'items' => $items, 'featured' => $featured,
            'tags' => $tags, 'tagCounts' => $tagCounts, 'activeTag' => $tag, 'search' => $search,
            'total' => Post::published()->count(),
        ]);
    }

    public function show(PortfolioProjects $portfolio, string $slug, ?string $locale = null)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Visitors only see published posts; the logged-in admin can preview drafts
        $preview = !$post->isPublished();
        abort_if($preview && !session('is_admin'), 404);

        $others = Post::published()->where('id', '!=', $post->id)->latest('published_at');
        $newer = $post->published_at ? (clone $others)->where('published_at', '>', $post->published_at)->reorder('published_at')->first() : null;
        $older = $post->published_at ? (clone $others)->where('published_at', '<', $post->published_at)->first() : null;

        $related = $this->related($post, $others);
        $relatedProjects = $this->relatedProjects($post, $portfolio);

        $article = $post->article();
        $body = $article['html'];
        $toc = $article['toc'];

        return view('blog.show', compact('post', 'preview', 'newer', 'older', 'related', 'relatedProjects', 'body', 'toc'));
    }

    public function feed()
    {
        $posts = Post::published()->latest('published_at')->limit(30)->get();

        return response()->view('blog.feed', compact('posts'))->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /** Up to two projects whose technologies match the post's tags (links readers to the case studies). */
    private function relatedProjects(Post $post, PortfolioProjects $portfolio)
    {
        $tags = array_map('mb_strtolower', $post->tags ?? []);
        if (! $tags) {
            return collect();
        }

        return $portfolio->all()
            ->filter(fn ($project) => count(array_intersect($tags, array_map('mb_strtolower', $project->tags))) > 0)
            ->take(2)
            ->values();
    }

    /** Up to three other published posts that share the most tags with this one. */
    private function related(Post $post, $others)
    {
        $tags = $post->tags ?? [];
        if (!$tags) {
            return collect();
        }

        return (clone $others)->limit(60)->get()
            ->map(fn (Post $p) => [$p, count(array_intersect($tags, $p->tags ?? []))])
            ->filter(fn ($pair) => $pair[1] > 0)
            ->sortByDesc(fn ($pair) => $pair[1])
            ->take(3)
            ->map(fn ($pair) => $pair[0])
            ->values();
    }
}
