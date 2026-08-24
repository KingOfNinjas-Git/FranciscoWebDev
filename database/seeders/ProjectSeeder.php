<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectImage;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update sample projects (idempotent)
        $p = Project::updateOrCreate(
            ['slug' => 'mae'],
            [
                'title' => 'MAE',
                'description' => 'MAE is a service-focused website built to present a company offering clear navigation, contact details, and a responsive layout. Stack: Laravel, Tailwind CSS, HTML, and Blade templates.',
                'technologies' => ['Laravel','Tailwind','Accessibility'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'A brochure-style website for a service business, designed around clarity and accessibility.'
            ]
        );

        // Add images (these reference files in public/imgs/)
        $images = [
            ['path' => 'imgs/maehome.jpg', 'alt' => 'MAE home', 'caption' => 'MAE homepage', 'position' => 0],
            ['path' => 'imgs/mae-features.jpg', 'alt' => 'MAE features', 'caption' => 'Features section', 'position' => 1],
            ['path' => 'imgs/mae-contact.jpg', 'alt' => 'MAE contact', 'caption' => 'Contact form', 'position' => 2],
        ];

        foreach ($images as $img) {
            ProjectImage::updateOrCreate(
                ['project_id' => $p->id, 'path' => $img['path']],
                array_merge($img, ['project_id' => $p->id])
            );
        }

        // Another sample
        $p2 = Project::updateOrCreate(
            ['slug' => 'gamehaven'],
            [
                'title' => 'GameHaven',
                'description' => 'GameHaven is a game catalogue landing page designed to organise featured titles and highlight promotional content in a straightforward interface. Stack: HTML, CSS, and JavaScript.',
                'technologies' => ['HTML','CSS','JavaScript'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'A simple landing page for presenting game titles and promotional sections in a clean structure.'
            ]
        );

        $images2 = [
            ['path' => 'imgs/Gamehaven.png', 'alt' => 'GameHaven', 'caption' => 'Landing', 'position' => 0],
            ['path' => 'imgs/Gamehaven-2.png', 'alt' => 'GameHaven features', 'caption' => 'Promo section', 'position' => 1],
        ];
        foreach ($images2 as $img) {
            ProjectImage::updateOrCreate(
                ['project_id' => $p2->id, 'path' => $img['path']],
                array_merge($img, ['project_id' => $p2->id])
            );
        }

        // Ensure Blog Page exists as well
        $p3 = Project::updateOrCreate(
            ['slug' => 'blog-page'],
            [
                'title' => 'Blog Page',
                'description' => 'Blog Page is a reading-focused layout for article discovery and blog content, with a clean structure for posts, tags, and responsive browsing. Stack: Laravel, PHP, and responsive front-end styling.',
                'technologies' => ['Laravel','Markdown','Responsive'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'A blog-style landing page focused on readability, navigation, and simple content organisation.'
            ]
        );
    }
}
