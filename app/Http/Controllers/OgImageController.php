<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PortfolioProjects;
use App\Services\ShareImage;
use Illuminate\Support\Facades\App;

/**
 * Share picture for blog posts and project pages that have no cover image (see ShareImage).
 */
class OgImageController extends Controller
{
    public function show(PortfolioProjects $portfolio, string $locale, string $type, string $slug)
    {
        App::setLocale($locale);

        if ($type === 'blog') {
            $post = Post::published()->where('slug', $slug)->first();
            abort_unless($post, 404);
            $title = $post->localized('title');
            $kicker = 'Blog · ' . __('site.blog.min_read', ['min' => $post->readingMinutes()]);
        } else {
            $project = $portfolio->all()->first(fn ($p) => $p->slug === $slug);
            abort_unless($project, 404);
            $title = $project->title;
            $kicker = __('site.projects.case_study');
        }

        $relative = ShareImage::path($type, $slug, $locale, $title, $kicker);
        $file = public_path($relative);
        $headers = ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400'];

        if (is_file($file)) {
            return response()->file($file, $headers);
        }

        $png = ShareImage::render($title, $kicker);
        if ($png === null) {
            return redirect(asset('imgs/og-image.png')); // this server cannot draw text
        }

        if (is_dir(dirname($file)) || @mkdir(dirname($file), 0755, true)) {
            @file_put_contents($file, $png);
        }

        return response($png, 200, $headers);
    }
}
