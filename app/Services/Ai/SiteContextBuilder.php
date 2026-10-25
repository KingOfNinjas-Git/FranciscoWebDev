<?php

namespace App\Services\Ai;

use App\Models\Post;
use App\Models\Project;
use App\Services\PortfolioProjects;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Builds the "what is already published" briefing the AI receives before it writes anything.
 *
 * It reads three things:
 *  1. the live website itself (public pages fetched over HTTP and parsed down to their readable text),
 *  2. the project records in the database (what exists, and which are new since the last published post),
 *  3. the existing blog posts (so the AI doesn't repeat topics and can link to earlier posts).
 */
class SiteContextBuilder
{
    private const CACHE_KEY = 'ai.site-context.v1';

    /**
     * @return array{text: string, stats: array}
     */
    public function build(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, config('ai.context_cache_seconds'), fn () => $this->assemble());
    }

    private function assemble(): array
    {
        $base = rtrim(config('ai.site_url') ?: config('app.url'), '/');
        $pages = [];
        $failed = [];

        foreach (['/' => 'Home page (about me, skills, projects, contact)', '/blog' => 'Blog index'] as $path => $label) {
            $text = $this->readPage($base . $path);
            if ($text === null && $path === '/') {
                $text = $this->renderHomeInternally();
            }
            if ($text === null) {
                $failed[] = $path;
                continue;
            }
            $pages[] = "### {$label} ({$path})\n" . $text;
        }

        [$projectsText, $projectCount, $newCount] = $this->describeProjects();
        [$postsText, $postCount] = $this->describePosts();

        $livePart = Str::limit(implode("\n\n", $pages), (int) (config('ai.context_chars') * 0.5), "\n[...live page text shortened...]");

        $text = "## 1. The live website (as visitors see it)\n"
            . ($livePart ?: '(the live site could not be read)')
            . "\n\n## 2. Projects already on the site\n" . $projectsText
            . "\n\n## 3. Blog posts that already exist\n" . $postsText;

        $text = Str::limit($text, config('ai.context_chars'), "\n[...context shortened...]");

        return [
            'text' => $text,
            'stats' => [
                'site_url' => $base,
                'pages_read' => count($pages),
                'pages_failed' => $failed,
                'projects' => $projectCount,
                'new_projects' => $newCount,
                'posts' => $postCount,
                'characters' => mb_strlen($text),
            ],
        ];
    }

    /** Fetch a public page and reduce it to readable text; null if it can't be fetched. */
    private function readPage(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->withUserAgent('PortfolioContextReader/1.0')->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful() || !str_contains((string) $response->header('Content-Type'), 'html')) {
            return null;
        }

        return $this->htmlToText($response->body());
    }

    /** If the site can't call itself (e.g. local development), render the home page directly. */
    private function renderHomeInternally(): ?string
    {
        try {
            $previous = App::getLocale();
            App::setLocale('en');
            $html = view('home', ['projects' => app(PortfolioProjects::class)->all()])->render();
            App::setLocale($previous);

            return $this->htmlToText($html);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function htmlToText(string $html): string
    {
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);

        // Drop everything that isn't page content
        foreach ($xpath->query('//script|//style|//noscript|//svg|//nav|//header|//footer|//form|//dialog|//button|//*[@aria-hidden="true"]') as $node) {
            $node->parentNode?->removeChild($node);
        }

        $lines = [];
        foreach ($xpath->query('//main//h1|//main//h2|//main//h3|//main//h4|//main//p|//main//li') as $node) {
            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            if ($text === '' || mb_strlen($text) < 2) continue;

            $lines[] = match ($node->nodeName) {
                'h1' => "# {$text}",
                'h2' => "## {$text}",
                'h3', 'h4' => "### {$text}",
                'li' => "- {$text}",
                default => $text,
            };
        }

        // Consecutive duplicates (repeated chips, etc.) add nothing
        $lines = array_values(array_filter($lines, fn ($l, $i) => $i === 0 || $l !== $lines[$i - 1], ARRAY_FILTER_USE_BOTH));

        return implode("\n", $lines);
    }

    /** @return array{0: string, 1: int, 2: int} */
    private function describeProjects(): array
    {
        try {
            $projects = Project::orderByDesc('created_at')->get();
            $lastPost = Post::published()->max('published_at');
        } catch (\Throwable $e) {
            return ['(project data unavailable)', 0, 0];
        }

        if ($projects->isEmpty()) {
            return ['(no projects have been added to the database yet; the site shows three sample projects)', 0, 0];
        }

        $new = 0;
        $lines = [];
        foreach ($projects as $p) {
            $isNew = !$lastPost || $p->created_at->gt($lastPost);
            $new += $isNew ? 1 : 0;

            $lines[] = '- ' . $p->title . ($isNew ? '  [NEW since the last published blog post]' : '')
                . ' (slug: ' . $p->slug . ', added ' . $p->created_at->toDateString() . ')'
                . ($p->technologies ? ' | tech: ' . implode(', ', $p->technologies) : '')
                . ($p->live_url ? ' | live: ' . $p->live_url : '')
                . ($p->repo_url ? ' | code: ' . $p->repo_url : '')
                . "\n  " . Str::limit(trim(preg_replace('/\s+/u', ' ', ($p->description ?? '') . ' ' . ($p->notes ?? ''))), 500);
        }

        return [implode("\n", $lines), $projects->count(), $new];
    }

    /** @return array{0: string, 1: int} */
    private function describePosts(): array
    {
        try {
            $posts = Post::orderByDesc('created_at')->limit(60)->get();
        } catch (\Throwable $e) {
            return ['(post data unavailable)', 0];
        }

        if ($posts->isEmpty()) {
            return ['(no blog posts yet; this will be the first one)', 0];
        }

        $lines = $posts->map(fn (Post $p) => '- "' . $p->title . '" (' . $p->status
            . ($p->published_at ? ', ' . $p->published_at->toDateString() : '')
            . ', url: /blog/' . $p->slug . ')'
            . ($p->tags ? ' tags: ' . implode(', ', $p->tags) : '')
            . ($p->excerpt ? "\n  " . Str::limit($p->excerpt, 200) : ''));

        return [$lines->implode("\n"), $posts->count()];
    }
}
