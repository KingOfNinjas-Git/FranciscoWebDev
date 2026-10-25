<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use App\Services\PortfolioProjects;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request, PortfolioProjects $projects, ?string $locale = null)
    {
        if ($locale === null) {
            // Returning visitors who picked Portuguese/Spanish land on that version of "/"
            $saved = $request->cookie('site_locale');
            if (in_array($saved, ['pt', 'es'], true)) {
                return redirect()->to(SetLocale::homeUrl($saved));
            }
        }

        try {
            $latestPosts = Post::published()->latest('published_at')->limit(3)->get();
        } catch (\Throwable $e) {
            $latestPosts = collect(); // the blog tables may not exist yet
        }

        return view('home', ['projects' => $projects->all(), 'latestPosts' => $latestPosts]);
    }

    public function sitemap(PortfolioProjects $projects)
    {
        return response()
            ->view('sitemap', [
                'locales' => SetLocale::LOCALES,
                'lastmod' => now()->toDateString(),
                'projects' => $projects->all(),
                'posts' => Post::published()->latest('published_at')->get(['slug', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
