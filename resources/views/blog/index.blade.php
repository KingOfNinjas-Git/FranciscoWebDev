@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::blogUrl(),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::blogUrl($l)])->all(),
])

@section('title', __('site.blog.meta_title'))
@section('description', __('site.blog.meta_description'))

@section('content')
@php $locale = app()->getLocale(); @endphp
<div class="mx-auto max-w-6xl">
    <header class="reveal mb-10">
        <p class="eyebrow">{{ __('site.blog.eyebrow') }}</p>
        <h1 class="section-title">{{ __('site.blog.title') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.blog.intro') }}</p>
    </header>

    @if ($tags->isNotEmpty())
        <nav class="reveal mb-10 flex flex-wrap items-center gap-2" aria-label="{{ __('site.blog.topics') }}">
            <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="tag-pill {{ $activeTag ? '' : 'is-active' }}" @if (!$activeTag) aria-current="true" @endif>{{ __('site.blog.all') }}</a>
            @foreach ($tags as $tag)
                <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}?tag={{ urlencode($tag) }}" class="tag-pill {{ $activeTag === $tag ? 'is-active' : '' }}" @if ($activeTag === $tag) aria-current="true" @endif>{{ $tag }}</a>
            @endforeach
        </nav>
    @endif

    @if ($posts->isEmpty())
        <p class="glass rounded-3xl p-10 text-center text-slate-400">{{ __('site.blog.empty') }}</p>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ \App\Http\Middleware\SetLocale::postUrl($post->slug) }}" class="reveal glass glass-hover group flex flex-col overflow-hidden rounded-3xl" style="--d:{{ ($loop->index % 3) * 90 }}ms">
                    <div class="project-media">
                        @if ($post->cover_path)
                            <img src="{{ $post->coverUrl() }}" alt="{{ $post->cover_alt }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-sky-500/20 to-violet-500/20 font-mono text-sm text-slate-500">&lt;/&gt;</div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="font-mono text-[11px] uppercase tracking-[0.2rem] text-slate-400">
                            {{ $post->published_at->locale($locale)->translatedFormat('d M Y') }} &middot; {{ __('site.blog.min_read', ['min' => $post->readingMinutes()]) }}
                        </p>
                        <h2 class="mt-2 text-xl font-semibold text-white">{{ $post->localized('title') }}</h2>
                        @if ($post->localized('excerpt'))
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-300">{{ $post->localized('excerpt') }}</p>
                        @endif
                        <div class="mt-auto flex flex-wrap gap-2 pt-4">
                            @foreach (array_slice($post->tags ?? [], 0, 3) as $tag)
                                <span class="chip">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <p class="mt-4 font-mono text-xs uppercase tracking-[0.2rem] text-sky-300 transition group-hover:text-white">{{ __('site.blog.read') }} &rarr;</p>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($posts->hasPages())
            <div class="mt-10 flex items-center justify-between gap-4">
                @if ($posts->previousPageUrl())
                    <a href="{{ $posts->previousPageUrl() }}" class="btn btn-ghost px-5 py-2.5 text-sm font-medium">&larr; {{ __('site.blog.newer') }}</a>
                @else
                    <span></span>
                @endif
                @if ($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}" class="btn btn-ghost px-5 py-2.5 text-sm font-medium">{{ __('site.blog.older') }} &rarr;</a>
                @endif
            </div>
        @endif
    @endif

    <p class="mt-12 text-center text-sm text-slate-500"><a href="{{ route('blog.feed') }}" class="hover:text-slate-300">{{ __('site.blog.rss') }}</a></p>
</div>
@endsection
