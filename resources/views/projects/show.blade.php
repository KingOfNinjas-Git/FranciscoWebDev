@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::projectUrl($project->slug),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::projectUrl($project->slug, $l)])->all(),
    'ogImage' => $project->cover ?? \App\Services\ShareImage::url('project', $project->slug, app()->getLocale()),
])

@section('title', $project->title . ' | Francisco Cavaco')
@section('description', $lead)

@push('head')
    @php
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $project->title,
            'description' => $lead,
            'url' => \App\Http\Middleware\SetLocale::projectUrl($project->slug),
            'image' => $project->cover,
            'inLanguage' => app()->getLocale(),
            'dateCreated' => $project->created,
            'keywords' => implode(', ', $project->tags),
            'author' => ['@type' => 'Person', 'name' => 'Francisco Cavaco', 'url' => \App\Http\Middleware\SetLocale::homeUrl()],
        ]);
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.nav.home'), 'item' => \App\Http\Middleware\SetLocale::homeUrl()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('site.nav.projects'), 'item' => \App\Http\Middleware\SetLocale::homeUrl() . '#projects'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $project->title, 'item' => \App\Http\Middleware\SetLocale::projectUrl($project->slug)],
            ],
        ];
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbs, $jsonFlags) !!}</script>
@endpush

@section('content')
@php
    $first = $project->images[0] ?? null;
    $firstSize = $first ? \App\Services\ImageVariants::size($first['path'] ?? '') : null;
@endphp
<article class="mx-auto max-w-4xl">
    <nav aria-label="Breadcrumb" class="reveal mb-6 text-sm text-slate-400">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}" class="hover:text-white">{{ __('site.nav.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}#projects" class="hover:text-white">{{ __('site.nav.projects') }}</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="text-slate-200">{{ $project->title }}</li>
        </ol>
    </nav>

    <header class="reveal mb-8">
        <p class="font-mono text-xs uppercase tracking-[0.2rem] text-sky-300">
            {{ __('site.projects.case_study') }}@if ($project->year) &middot; {{ $project->year }}@endif
        </p>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-5xl">{{ $project->title }}</h1>
        @if ($project->description)
            <p class="mt-4 text-lg leading-8 text-slate-300">{{ $project->description }}</p>
        @endif

        @if ($project->tags)
            <div class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('site.projects.built_with') }}">
                @foreach ($project->tags as $tag)
                    <span class="chip">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        @if ($project->live_url || $project->repo_url)
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($project->live_url)
                    <a href="{{ $project->live_url }}" data-track="project_live" target="_blank" rel="noopener noreferrer" class="btn btn-primary px-5 py-2.5 text-sm font-semibold">{{ __('site.projects.live') }} &nearr;</a>
                @endif
                @if ($project->repo_url)
                    <a href="{{ $project->repo_url }}" data-track="project_repo" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-5 py-2.5 text-sm font-semibold">{{ __('site.projects.repo') }} &nearr;</a>
                @endif
            </div>
        @endif
    </header>

    @if ($first)
        {{-- The first screenshot is real HTML (fast, works without JavaScript); a script upgrades it to a swipeable gallery --}}
        <div id="project-gallery" class="reveal mb-12" data-title="{{ $project->title }}" data-l-prev="{{ __('site.projects.prev') }}" data-l-next="{{ __('site.projects.next') }}">
            <figure class="overflow-hidden rounded-3xl border border-white/10">
                <img src="{{ $first['url'] }}" @if ($first['srcset']) srcset="{{ $first['srcset'] }}" sizes="(min-width: 896px) 896px, 100vw" @endif
                     @if ($firstSize) width="{{ $firstSize[0] }}" height="{{ $firstSize[1] }}" @endif
                     alt="{{ $first['alt'] }}" fetchpriority="high" decoding="async" class="h-auto w-full object-cover">
                @if ($first['caption'])<figcaption class="px-4 py-2 text-center text-sm text-slate-400">{{ $first['caption'] }}</figcaption>@endif
            </figure>
            @if (count($project->images) > 1)
                <noscript>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach (array_slice($project->images, 1) as $image)
                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" class="h-auto w-full rounded-2xl border border-white/10">
                        @endforeach
                    </div>
                </noscript>
            @endif
        </div>
        @if (count($project->images) > 1)
            <script type="application/json" id="project-gallery-data">@json($project->images)</script>
        @endif
    @endif

    @if ($notesHtml)
        <div class="prose-blog">{!! $notesHtml !!}</div>
    @endif

    <aside class="glass reveal mt-14 rounded-3xl p-6 text-center md:p-10" aria-labelledby="cta-title">
        <h2 id="cta-title" class="text-2xl font-bold text-white">{{ __('site.projects.cta_title') }}</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-300">{{ __('site.projects.cta_text') }}</p>
        <a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}#contact" data-track="project_cta" class="btn btn-primary mt-6 px-7 py-3 text-sm font-semibold">{{ __('site.projects.cta_button') }}</a>
    </aside>

    @if ($related->isNotEmpty())
        <section class="mt-14" aria-labelledby="related-heading">
            <h2 id="related-heading" class="mb-5 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ __('site.blog.related') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ \App\Http\Middleware\SetLocale::postUrl($item->slug) }}" class="glass glass-hover rounded-2xl p-4">
                        <span class="block text-xs text-slate-400">{{ __('site.blog.min_read', ['min' => $item->readingMinutes()]) }}</span>
                        <span class="mt-1 block font-semibold text-white">{{ $item->localized('title') }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($previous || $next)
        <nav class="mt-14 grid gap-4 border-t border-white/10 pt-8 sm:grid-cols-2" aria-label="{{ __('site.nav.projects') }}">
            @if ($previous)
                <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($previous->slug) }}" class="glass glass-hover rounded-2xl p-4">
                    <span class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">&larr; {{ __('site.projects.previous_project') }}</span>
                    <span class="mt-1 block font-semibold text-white">{{ $previous->title }}</span>
                </a>
            @else
                <span></span>
            @endif
            @if ($next)
                <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($next->slug) }}" class="glass glass-hover rounded-2xl p-4 sm:text-right">
                    <span class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">{{ __('site.projects.next_project') }} &rarr;</span>
                    <span class="mt-1 block font-semibold text-white">{{ $next->title }}</span>
                </a>
            @endif
        </nav>
    @endif
</article>
@endsection
