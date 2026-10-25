<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class BlogController extends Controller
{
    public function index(Request $request, ?string $locale = null)
    {
        $this->rememberLocale($locale);

        $tag = $request->query('tag');
        $query = Post::published()->latest('published_at');
        if ($tag) {
            $query->whereJsonContains('tags', (string) $tag);
        }

        $posts = $query->simplePaginate(9)->withQueryString();

        $tags = Post::published()->pluck('tags')->flatten()->filter()->countBy()->sortDesc()->keys()->take(20);

        return view('blog.index', ['posts' => $posts, 'tags' => $tags, 'activeTag' => $tag]);
    }

    public function show(string $slug, ?string $locale = null)
    {
        $this->rememberLocale($locale);

        $post = Post::where('slug', $slug)->firstOrFail();

        // Visitors only see published posts; the logged-in admin can preview drafts
        $preview = !$post->isPublished();
        abort_if($preview && !session('is_admin'), 404);

        $others = Post::published()->where('id', '!=', $post->id)->latest('published_at');
        $newer = $post->published_at ? (clone $others)->where('published_at', '>', $post->published_at)->reorder('published_at')->first() : null;
        $older = $post->published_at ? (clone $others)->where('published_at', '<', $post->published_at)->first() : null;

        return view('blog.show', compact('post', 'preview', 'newer', 'older'));
    }

    public function feed()
    {
        $posts = Post::published()->latest('published_at')->limit(30)->get();

        return response()->view('blog.feed', compact('posts'))->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function rememberLocale(?string $locale): void
    {
        if ($locale) {
            Cookie::queue('site_locale', $locale, 60 * 24 * 365);
        }
    }
}
