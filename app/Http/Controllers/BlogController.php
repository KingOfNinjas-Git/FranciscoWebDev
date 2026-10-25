<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
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

        $tags = Post::published()->pluck('tags')->flatten()->filter()->countBy()->sortDesc()->keys()->take(20);

        return view('blog.index', ['posts' => $posts, 'tags' => $tags, 'activeTag' => $tag, 'search' => $search]);
    }

    public function show(string $slug, ?string $locale = null)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Visitors only see published posts; the logged-in admin can preview drafts
        $preview = !$post->isPublished();
        abort_if($preview && !session('is_admin'), 404);

        $others = Post::published()->where('id', '!=', $post->id)->latest('published_at');
        $newer = $post->published_at ? (clone $others)->where('published_at', '>', $post->published_at)->reorder('published_at')->first() : null;
        $older = $post->published_at ? (clone $others)->where('published_at', '<', $post->published_at)->first() : null;

        $related = $this->related($post, $others);

        return view('blog.show', compact('post', 'preview', 'newer', 'older', 'related'));
    }

    public function feed()
    {
        $posts = Post::published()->latest('published_at')->limit(30)->get();

        return response()->view('blog.feed', compact('posts'))->header('Content-Type', 'application/rss+xml; charset=UTF-8');
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
