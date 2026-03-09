import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/common.js',
                'resources/js/navbar.js',
                'resources/js/home.js',
                'resources/js/about.js',
                'resources/js/projects.js',
                'resources/js/project-show.js',
                'resources/js/contact.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});