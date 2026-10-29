<?php

namespace App\Http\Controllers;

use App\Services\DemoStatus;
use App\Services\PortfolioProjects;

class PageController extends Controller
{
    public function privacy(?string $locale = null)
    {
        return view('privacy');
    }

    public function accessibility(?string $locale = null)
    {
        return view('accessibility');
    }

    /** Every project that has a live demo, with a green or red light for "is it answering right now". */
    public function lab(PortfolioProjects $portfolio, ?string $locale = null)
    {
        $demos = $portfolio->all()->filter(fn ($project) => filled($project->live_url))->values();
        $status = DemoStatus::checkMany($demos->pluck('live_url')->all());

        return view('lab', ['demos' => $demos, 'status' => $status]);
    }
}
