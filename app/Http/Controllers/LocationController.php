<?php

namespace App\Http\Controllers;

/**
 * "Web developer in <town>": one page per town listed in config/locations.php, in the language of the address.
 */
class LocationController extends Controller
{
    public function show(string $town, ?string $locale = null)
    {
        $data = config("locations.$town");
        abort_if($data === null, 404);

        $locale = app()->getLocale();

        return view('location', [
            'slug' => $town,
            'name' => $data['name'],
            'blurb' => $data['blurb'][$locale] ?? $data['blurb']['en'],
            'near' => $data['near'][$locale] ?? $data['near']['en'],
            'businesses' => $data['businesses'][$locale] ?? $data['businesses']['en'],
            'places' => $data['places'] ?? [],
            'others' => collect(config('locations'))->except($town)->map(fn ($item) => $item['name']),
        ]);
    }
}
