<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Post;
use App\Services\PortfolioProjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        } else {
            // Arriving on /pt or /es (e.g. from a shared link) also remembers the choice
            Cookie::queue('site_locale', $locale, 60 * 24 * 365);
        }

        return view('home', ['projects' => $projects->all()]);
    }

    public function sitemap()
    {
        return response()
            ->view('sitemap', [
                'locales' => SetLocale::LOCALES,
                'lastmod' => now()->toDateString(),
                'posts' => Post::published()->latest('published_at')->get(['slug', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
