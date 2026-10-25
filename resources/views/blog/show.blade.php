@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::postUrl($post->slug),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::postUrl($post->slug, $l)])->all(),
    'ogType' => 'article',
    'ogImage' => $post->coverUrl() ?? asset('imgs/og-image.png'),
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
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endif
@endpush

@section('content')
@php $locale = app()->getLocale(); @endphp
<article class="mx-auto max-w-3xl">
    @if ($preview)
        <p role="status" class="mb-6 rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">{{ __('site.blog.draft_notice') }}</p>
    @endif

    <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="reveal btn btn-ghost mb-8 px-4 py-2 text-sm font-medium">&larr; {{ __('site.blog.all_posts') }}</a>

    <header class="reveal mb-8">
        <p class="font-mono text-xs uppercase tracking-[0.2rem] text-sky-300">
            @if ($post->published_at)
                {{ $post->published_at->locale($locale)->translatedFormat('d M Y') }} &middot;
            @endif
            {{ __('site.blog.min_read', ['min' => $post->readingMinutes()]) }}
        </p>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-5xl">{{ $post->localized('title') }}</h1>
        @if ($post->localized('excerpt'))
            <p class="mt-4 text-lg leading-8 text-slate-300">{{ $post->localized('excerpt') }}</p>
        @endif
        @if ($post->tags)
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}?tag={{ urlencode($tag) }}" class="chip hover:border-sky-400">{{ $tag }}</a>
                @endforeach
            </div>
        @endif
    </header>

    @if ($post->cover_path)
        <figure class="reveal mb-10 overflow-hidden rounded-3xl border border-white/10">
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->cover_alt }}" class="w-full object-cover">
        </figure>
    @endif

    <div class="prose-blog">{!! $post->html() !!}</div>

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
            <button type="button" data-share-native hidden class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ __('site.blog.share_native') }}</button>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm font-medium">LinkedIn</a>
            <a href="https://wa.me/?text={{ urlencode($shareTitle . ' ' . $shareUrl) }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm font-medium">WhatsApp</a>
            <a href="mailto:?subject={{ rawurlencode($shareTitle) }}&amp;body={{ rawurlencode($shareUrl) }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">Email</a>
            <button type="button" data-copy-link class="btn btn-ghost px-4 py-2 text-sm font-medium">{{ __('site.blog.copy_link') }}</button>
            <span data-copy-status class="text-sm text-emerald-300" role="status" aria-live="polite"></span>
        </div>
    @endunless

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

    @if ($newer || $older)
        <nav class="mt-14 grid gap-4 border-t border-white/10 pt-8 sm:grid-cols-2" aria-label="{{ __('site.blog.more') }}">
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
</article>
@endsection
