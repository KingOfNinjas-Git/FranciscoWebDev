<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->title ?? 'Project' }} | Francisco Cavaco</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($project->description ?? 'A web development project by Francisco Cavaco.'), 160) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $project->title ?? 'Project' }} | Francisco Cavaco">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($project->description ?? 'A web development project by Francisco Cavaco.'), 200) }}">
    <meta name="twitter:card" content="summary">
    @vite(['resources/css/app.css'])
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-6xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <div class="mb-8">
            <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-slate-500 hover:bg-slate-800">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to projects
            </a>
        </div>

        @php
            $images = isset($project->images) && is_iterable($project->images) ? $project->images : [];
            $techs = $project->technologies ?? ($project->tags ?? []);
            $techList = is_array($techs) ? $techs : explode(',', $techs);
        @endphp

        <div class="grid gap-8 items-start lg:grid-cols-[1.1fr_0.9fr]">
            <section class="site-card overflow-hidden rounded-3xl border border-slate-800 p-3">
                @if (!empty($images))
                                    <img src="{{ asset($images[0]->public_path ?? $images[0]->path) }}" alt="{{ $project->title }}" class="w-full rounded-2xl">
                @elseif (!empty($project->image))
                                    <img src="{{ asset($project->image) }}" alt="{{ $project->title }}" class="w-full rounded-2xl">
                @else
                    <div class="flex h-[420px] items-center justify-center rounded-2xl border border-dashed border-slate-700 bg-slate-900 text-slate-400">
                        Project image unavailable
                    </div>
                @endif
            </section>

            <article class="site-card rounded-3xl border border-slate-800 p-6 md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.24rem] text-sky-300">Project</p>
                <h1 class="mt-3 text-3xl font-semibold text-white md:text-5xl">{{ $project->title }}</h1>

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-200">{{ $project->category ?? 'Web project' }}</span>
                    @if (!empty($project->created_at))
                        <span class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-200">{{ $project->created_at->format('F Y') }}</span>
                    @endif
                </div>

                <p class="mt-6 text-lg leading-8 text-slate-300">{{ $project->description ?? '' }}</p>

                <div class="mt-8">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.22rem] text-slate-400">Stack</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($techList as $tech)
                            <span class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-200">{{ trim($tech) }}</span>
                        @endforeach
                    </div>
                </div>

                @if (!empty($project->notes))
                    <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                        <h2 class="text-lg font-semibold text-white">Project details</h2>
                        <div class="mt-3 whitespace-pre-wrap text-base leading-7 text-slate-300">{{ $project->notes }}</div>
                    </div>
                @endif

                @if ((!empty($project->live_url) && $project->live_url !== '#') || (!empty($project->repo_url) && $project->repo_url !== '#'))
                    <div class="mt-8 flex flex-wrap gap-3">
                        @if (!empty($project->live_url) && $project->live_url !== '#')
                            <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer" class="site-button site-button-primary px-5 py-3 text-sm font-medium text-white">Visit live site</a>
                        @endif
                        @if (!empty($project->repo_url) && $project->repo_url !== '#')
                            <a href="{{ $project->repo_url }}" target="_blank" rel="noopener noreferrer" class="site-button px-5 py-3 text-sm font-medium text-slate-200">View source code</a>
                        @endif
                    </div>
                @endif
            </article>
        </div>
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js'])
</body>
</html>
