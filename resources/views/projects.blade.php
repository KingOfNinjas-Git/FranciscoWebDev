<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects | Francisco Cavaco</title>
    <meta name="description" content="Explore Francisco Cavaco's web development projects, including the technologies, design decisions, and practical problems behind each one.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="My Projects | Francisco Cavaco">
    <meta property="og:description" content="A selection of web development projects built with clear interfaces and maintainable code.">
    <meta name="twitter:card" content="summary">
    @vite(['resources/css/app.css'])
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-6xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <header class="mb-12 text-center md:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Projects</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white md:text-6xl">My work</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-slate-300">
                A small set of projects built to solve specific problems using clear interfaces and maintainable code.
            </p>
        </header>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($projects as $project)
                <article class="project-card flex h-full flex-col overflow-hidden rounded-3xl">
                   <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" class="block overflow-hidden">
                       <img loading="lazy" src="{{ $project->image_url }}" alt="{{ $project->title }}" class="project-card-image transition duration-300 hover:scale-[1.02]">
                   </a>

                   <div class="flex flex-1 flex-col p-5">
                       <h2 class="text-xl font-semibold text-white">{{ $project->title }}</h2>
                       <p class="mt-3 flex-1 text-sm leading-7 text-slate-300">{{ $project->description }}</p>

                       <div class="mt-5 flex flex-wrap gap-2">
                           @foreach (array_slice($project->tags ?? [], 0, 4) as $tag)
                               <span class="rounded-full border border-slate-700 bg-slate-900 px-2.5 py-1 text-xs font-medium text-slate-200">{{ trim($tag) }}</span>
                           @endforeach
                       </div>

                       <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" class="site-button site-button-primary mt-6 w-full px-4 py-3 text-sm font-medium text-white">
                           View project
                       </a>
                   </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center text-slate-300">
                   No projects to display at the moment.
                </div>
            @endforelse
        </div>
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/projects.js'])
</body>
</html>
