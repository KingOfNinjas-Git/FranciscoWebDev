<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Services\Ai\AiWriter;
use App\Services\ImageOptimizer;
use App\Services\ImageVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostAdminController extends Controller
{
    public function index()
    {
        $posts = Post::orderByRaw("status = 'published' desc")->orderByDesc('published_at')->orderByDesc('created_at')->get();
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.form', $this->formData(null));
    }

    public function store(Request $request, ImageOptimizer $optimizer)
    {
        $data = $this->validated($request);

        $post = new Post();
        $post->slug = $this->uniqueSlug($data['title']);
        $this->apply($post, $request, $data, $optimizer);
        $post->save();

        return redirect()->route('admin.posts.edit', $post->id)->with('success', 'Post saved.');
    }

    public function edit($id)
    {
        return view('admin.posts.form', $this->formData(Post::findOrFail($id)));
    }

    public function update(Request $request, $id, ImageOptimizer $optimizer)
    {
        $post = Post::findOrFail($id);
        $data = $this->validated($request);

        // The slug stays stable so published links keep working
        $this->apply($post, $request, $data, $optimizer);
        $post->save();

        return redirect()->route('admin.posts.edit', $post->id)->with('success', 'Post saved.');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->cover_path && is_file(public_path($post->cover_path))) {
            @unlink(public_path($post->cover_path));
            ImageVariants::delete($post->cover_path);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }

    // Renders Markdown with the same safe renderer the public page uses
    public function preview(Request $request)
    {
        $request->validate(['body' => 'nullable|string|max:100000']);

        return response()->json(['html' => Post::renderMarkdown((string) $request->input('body'))]);
    }

    private function formData(?Post $post): array
    {
        return [
            'post' => $post,
            'projects' => Project::orderBy('title')->get(['title', 'slug']),
            'types' => AiWriter::TYPES,
            'tones' => AiWriter::TONES,
            'lengths' => AiWriter::LENGTHS,
            'aiReady' => filled(config('ai.api_key')),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:600',
            'body' => 'required|string|max:100000',
            'tags' => 'nullable|string|max:300',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'ai_assisted' => 'sometimes|boolean',
            'cover' => 'nullable|image|max:5120',
            'cover_alt' => 'nullable|string|max:255',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.excerpt' => 'nullable|string|max:600',
            'translations.*.body' => 'nullable|string|max:100000',
        ]);
    }

    private function apply(Post $post, Request $request, array $data, ImageOptimizer $optimizer): void
    {
        $post->title = $data['title'];
        $post->excerpt = $data['excerpt'] ?? null;
        $post->body = $data['body'];
        $post->tags = array_values(array_unique(array_filter(array_map(
            fn ($t) => Str::lower(trim($t)),
            explode(',', (string) ($data['tags'] ?? ''))
        ))));
        $post->cover_alt = $data['cover_alt'] ?? null;
        $post->ai_assisted = $request->boolean('ai_assisted');
        $post->translations = $this->parseTranslations($request);

        $post->status = $data['status'];
        if ($post->status === 'published') {
            // Publishing without a date means "now"; a future date schedules the post
            $post->published_at = !empty($data['published_at'])
                ? \Illuminate\Support\Carbon::parse($data['published_at'])
                : ($post->published_at ?? now());
        } else {
            $post->published_at = !empty($data['published_at']) ? \Illuminate\Support\Carbon::parse($data['published_at']) : $post->published_at;
        }

        if ($request->hasFile('cover')) {
            $dir = public_path('imgs/blog');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            if ($post->cover_path && is_file(public_path($post->cover_path))) {
                @unlink(public_path($post->cover_path));
                ImageVariants::delete($post->cover_path);
            }
            $post->cover_path = 'imgs/blog/' . $optimizer->store($request->file('cover'), $dir);
            ImageVariants::make($post->cover_path);
        }
    }

    private function parseTranslations(Request $request): ?array
    {
        $out = [];
        foreach (Post::TRANSLATION_LOCALES as $locale) {
            foreach (Post::TRANSLATABLE as $field) {
                $value = trim((string) $request->input("translations.$locale.$field"));
                if ($value !== '') $out[$locale][$field] = $value;
            }
        }
        return $out ?: null;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post-' . time();
        $slug = $base;
        for ($i = 2; Post::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }
}
