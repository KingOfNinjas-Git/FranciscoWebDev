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
                'description' => 'MAE — a responsive, accessible website showcasing services and contact features. Built with semantic HTML, Tailwind CSS and Laravel Blade.',
                'technologies' => ['Laravel','Tailwind','Accessibility'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'Sample seeded project for demo purposes.'
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
                'description' => 'GameHaven — a game catalogue and community landing page with interactive UI elements and launch promos.',
                'technologies' => ['HTML','CSS','JavaScript'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'Seeded project with sample images.'
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
                'description' => 'Blog Page — a clean blog layout with article lists, tags, and accessible reading experience. Perfect for technical writing and tutorials.',
                'technologies' => ['Laravel','Markdown','Responsive'],
                'live_url' => '#',
                'repo_url' => '#',
                'notes' => 'Seeded blog project.'
            ]
        );
    }
}
