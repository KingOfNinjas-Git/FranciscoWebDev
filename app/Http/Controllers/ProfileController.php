<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use App\Models\Testimonial;
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

        try {
            $testimonials = Testimonial::published()->get();
        } catch (\Throwable $e) {
            $testimonials = collect(); // the table may not exist yet
        }

        return view('home', ['projects' => $projects->all(), 'latestPosts' => $latestPosts, 'testimonials' => $testimonials]);
    }

    public function sitemap(PortfolioProjects $projects)
    {
        $all = $projects->all();
        $posts = Post::published()->latest('published_at')->get(['slug', 'updated_at']);

        // The newest change to anything shown on a page decides that page's date
        $newestPost = $posts->max('updated_at')?->toDateString();
        $newestProject = $all->max('updated');
        $lastmods = [
            'home' => collect([config('portfolio.site_updated'), $newestPost, $newestProject])->filter()->max(),
            'blog' => collect([$newestPost, config('portfolio.site_updated')])->filter()->max(),
            'privacy' => config('portfolio.privacy_updated'),
            'lab' => collect([$newestProject, config('portfolio.site_updated')])->filter()->max(),
        ];

        return response()
            ->view('sitemap', [
                'locales' => SetLocale::LOCALES,
                'lastmods' => $lastmods,
                'projects' => $all,
                'posts' => $posts,
            ])
            ->header('Content-Type', 'application/xml');
    }
}
