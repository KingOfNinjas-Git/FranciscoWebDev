@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::postUrl($post->slug),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::postUrl($post->slug, $l)])->all(),
    'ogType' => 'article',
    'ogImage' => $post->coverUrl() ?? ($preview ? asset('imgs/og-image.png') : \App\Services\ShareImage::url('blog', $post->slug, app()->getLocale())),
    'noindex' => $preview,
])

@section('title', $post->localized('title') . ' | Francisco Cavaco')
@section('description', $post->localized('excerpt') ?: \Illuminate\Support\Str::limit(strip_tags($post->html()), 160))

@push('head')
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->localized('title'),
            'description' => $post->localized('excerpt'),
            'image' => $post->coverUrl(),
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'inLanguage' => app()->getLocale(),
            'keywords' => implode(', ', $post->tags ?? []),
            'mainEntityOfPage' => \App\Http\Middleware\SetLocale::postUrl($post->slug),
            'author' => ['@type' => 'Person', 'name' => 'Francisco Cavaco', 'url' => \App\Http\Middleware\SetLocale::homeUrl()],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @php
        $crumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.nav.home'), 'item' => \App\Http\Middleware\SetLocale::homeUrl()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('site.nav.blog'), 'item' => \App\Http\Middleware\SetLocale::blogUrl()],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->localized('title'), 'item' => \App\Http\Middleware\SetLocale::postUrl($post->slug)],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($crumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endif
@endpush

@section('content')
@php
    $locale = app()->getLocale();
    $blogUrl = \App\Http\Middleware\SetLocale::blogUrl();
    $showToc = count($toc) >= 3;
    $updated = $post->published_at && $post->updated_at && $post->updated_at->gt($post->published_at->copy()->addDay());
@endphp
@unless ($preview)
    <div class="read-progress" aria-hidden="true"><span id="read-progress-bar"></span></div>
@endunless
<div class="mx-auto max-w-6xl">
    @if ($preview)
        <p role="status" class="mx-auto mb-6 max-w-3xl rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">{{ __('site.blog.draft_notice') }}</p>
    @endif

    <div class="blog-layout {{ $showToc ? 'has-toc' : '' }}">
        @if ($showToc)
            <aside class="blog-aside" aria-label="{{ __('site.blog.toc') }}">
                <details class="toc glass rounded-2xl" data-toc>
                    <summary class="toc-summary">
                        <span class="font-mono text-xs uppercase tracking-[0.2rem] text-sky-300">{{ __('site.blog.toc') }}</span>
                        <svg class="toc-chevron h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <nav class="toc-body" aria-label="{{ __('site.blog.toc') }}">
                        <ol>
                            @foreach ($toc as $item)
                                <li class="{{ $item['level'] === 3 ? 'toc-sub' : '' }}"><a href="#{{ $item['id'] }}" data-toc-link>{{ $item['text'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                </details>
            </aside>
        @endif

        <article class="blog-article" data-article data-l-copy="{{ __('site.blog.copy_code') }}" data-l-copied="{{ __('site.blog.code_copied') }}" data-l-copy-label="{{ __('site.blog.copy_code_label') }}">
            <a href="{{ $blogUrl }}" class="reveal btn btn-ghost mb-8 px-4 py-2 text-sm font-medium">&larr; {{ __('site.blog.all_posts') }}</a>

            <header class="reveal mb-8">
                <div class="mb-5 flex flex-wrap items-center gap-x-4 gap-y-3">
                    <span class="author-avatar" aria-hidden="true">FC</span>
                    <div class="text-sm leading-5">
                        <p class="font-semibold text-white"><span class="text-slate-400">{{ __('site.blog.by') }}</span> Francisco Cavaco</p>
                        <p class="font-mono text-[11px] uppercase tracking-[0.15rem] text-slate-400">
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($locale)->translatedFormat('d M Y') }}</time> &middot;
                            @endif
                            {{ __('site.blog.min_read', ['min' => $post->readingMinutes()]) }}
                            @if ($updated)
                                &middot; {{ __('site.blog.updated') }} <time datetime="{{ $post->updated_at->toDateString() }}">{{ $post->updated_at->locale($locale)->translatedFormat('d M Y') }}</time>
                            @endif
                        </p>
                    </div>
                </div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-white sm:text-5xl">{{ $post->localized('title') }}</h1>
                @if ($post->localized('excerpt'))
                    <p class="mt-4 text-lg leading-8 text-slate-300">{{ $post->localized('excerpt') }}</p>
                @endif
                @if ($post->tags)
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <a href="{{ $blogUrl }}?tag={{ urlencode($tag) }}" class="chip hover:border-sky-400">{{ $tag }}</a>
                        @endforeach
                    </div>
                @endif
            </header>

            @if ($post->cover_path)
                <figure class="reveal mb-10 overflow-hidden rounded-3xl border border-white/10">
                    @php $cs = $post->coverSize(); $srcset = $post->coverSrcset(); @endphp
                    <img src="{{ $post->coverUrl() }}" @if ($srcset) srcset="{{ $srcset }}" sizes="(min-width: 768px) 768px, 100vw" @endif @if ($cs) width="{{ $cs[0] }}" height="{{ $cs[1] }}" @endif alt="{{ $post->cover_alt }}" fetchpriority="high" decoding="async" class="h-auto w-full object-cover">
                </figure>
            @endif

            <div class="prose-blog">{!! $body !!}</div>

            @if ($post->ai_assisted)
                <p class="mt-10 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-400">{{ __('site.blog.ai_note') }}</p>
            @endif

            @php
                $shareUrl = \App\Http\Middleware\SetLocale::postUrl($post->slug);
                $shareTitle = $post->localized('title');
            @endphp
            @unless ($preview)
                <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-white/10 pt-6" data-share data-copied="{{ __('site.blog.copied') }}" data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}">
                    <span class="font-mono text-xs uppercase tracking-[0.2rem] text-slate-400">{{ __('site.blog.share') }}</span>
                    <button type="button" data-share-native hidden data-track="blog_share" class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ __('site.blog.share_native') }}</button>
                    <a data-track="blog_share" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm font-medium">LinkedIn</a>
                    <a data-track="blog_share" href="https://wa.me/?text={{ urlencode($shareTitle . ' ' . $shareUrl) }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm font-medium">WhatsApp</a>
                    <a data-track="blog_share" href="mailto:?subject={{ rawurlencode($shareTitle) }}&amp;body={{ rawurlencode($shareUrl) }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">Email</a>
                    <button type="button" data-copy-link data-track="blog_share" class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ __('site.blog.copy_link') }}</button>
                    <span data-copy-status class="text-sm text-emerald-300" role="status" aria-live="polite"></span>
                </div>
            @endunless

            <aside class="author-box glass reveal mt-10 rounded-3xl p-6 sm:p-8" aria-labelledby="author-heading">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <span class="author-avatar author-avatar-lg" aria-hidden="true">FC</span>
                    <div class="flex-1">
                        <h2 id="author-heading" class="text-lg font-semibold text-white">{{ __('site.blog.author_title') }}</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-300">{{ __('site.blog.author_text') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:flex-col">
                        <a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}#contact" data-track="blog_cta" class="btn btn-primary px-5 py-2.5 text-sm font-semibold">{{ __('site.blog.author_button') }}</a>
                        <a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}#about" class="btn btn-ghost px-5 py-2.5 text-sm font-medium">{{ __('site.blog.about_me') }}</a>
                    </div>
                </div>
            </aside>

            @if ($relatedProjects->isNotEmpty())
                <section class="mt-14" aria-labelledby="related-projects-heading">
                    <h2 id="related-projects-heading" class="mb-5 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ __('site.blog.related_projects') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($relatedProjects as $project)
                            <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug) }}" class="glass glass-hover rounded-2xl p-4">
                                <span class="block text-xs text-slate-400">{{ __('site.blog.related_projects_open') }}</span>
                                <span class="mt-1 block font-semibold text-white">{{ $project->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
    </div>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-16 max-w-6xl" aria-labelledby="related-heading">
            <h2 id="related-heading" class="mb-5 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ __('site.blog.related') }}</h2>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($related as $item)
                    @include('blog._card', ['post' => $item, 'delay' => $loop->index * 90])
                @endforeach
            </div>
        </section>
    @endif

    @if ($newer || $older)
        <nav class="mx-auto mt-14 grid max-w-3xl gap-4 border-t border-white/10 pt-8 sm:grid-cols-2" aria-label="{{ __('site.blog.more') }}">
            @if ($older)
                <a href="{{ \App\Http\Middleware\SetLocale::postUrl($older->slug) }}" class="glass glass-hover rounded-2xl p-4">
                    <span class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">&larr; {{ __('site.blog.older') }}</span>
                    <span class="mt-1 block font-semibold text-white">{{ $older->localized('title') }}</span>
                </a>
            @else
                <span></span>
            @endif
            @if ($newer)
                <a href="{{ \App\Http\Middleware\SetLocale::postUrl($newer->slug) }}" class="glass glass-hover rounded-2xl p-4 sm:text-right">
                    <span class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">{{ __('site.blog.newer') }} &rarr;</span>
                    <span class="mt-1 block font-semibold text-white">{{ $newer->localized('title') }}</span>
                </a>
            @endif
        </nav>
    @endif
</div>
@endsection
