@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::blogUrl(),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::blogUrl($l)])->all(),
    'noindex' => filled($search ?? null),
])

@section('title', __('site.blog.meta_title'))
@section('description', __('site.blog.meta_description'))

@section('content')
@php
    $blogUrl = \App\Http\Middleware\SetLocale::blogUrl();
    $visibleTags = $tags->take(6);
    $moreTags = $tags->slice(6)->values();
    $moreOpen = $activeTag && $moreTags->contains($activeTag);
@endphp
<div class="mx-auto max-w-6xl">
    <header class="reveal mb-10 flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="eyebrow">{{ __('site.blog.eyebrow') }}</p>
            <h1 class="section-title">{{ __('site.blog.title') }}</h1>
            <p class="mt-3 text-slate-400">{{ __('site.blog.intro') }}</p>
            <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-400">
                <span>{{ trans_choice('site.blog.posts_count', $total, ['count' => $total]) }}</span>
                <span aria-hidden="true">&middot;</span>
                <span>{{ trans_choice('site.blog.topics_count', $tags->count(), ['count' => $tags->count()]) }}</span>
            </p>
        </div>
        <a href="{{ route('blog.feed') }}" class="btn btn-ghost gap-2 px-4 py-2 text-sm font-medium" data-track="blog_rss">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="19" r="2.2"/><path d="M3 10.5v3.2A7.3 7.3 0 0 1 10.3 21h3.2C13.5 14.9 8.6 10.5 3 10.5Zm0-6.3v3.2c7.6 0 13.6 6 13.6 13.6h3.2C19.8 11.7 12.3 4.2 3 4.2Z"/></svg>
            {{ __('site.blog.rss') }}
        </a>
    </header>

    <form method="GET" action="{{ $blogUrl }}" role="search" class="reveal mb-6 flex max-w-xl gap-2">
        @if ($activeTag)<input type="hidden" name="tag" value="{{ $activeTag }}">@endif
        <label class="field flex-1">
            <span class="sr-only">{{ __('site.blog.search_label') }}</span>
            <input type="search" name="q" value="{{ $search }}" maxlength="80" placeholder="{{ __('site.blog.search_ph') }}" autocomplete="off">
        </label>
        <button type="submit" class="btn btn-primary px-5 text-sm font-semibold">{{ __('site.blog.search_btn') }}</button>
    </form>

    @if (filled($search))
        <p class="mb-6 text-slate-300" role="status">
            {{ __('site.blog.results_for', ['q' => $search]) }}
            &middot; <a href="{{ $blogUrl }}{{ $activeTag ? '?tag=' . urlencode($activeTag) : '' }}" class="text-sky-300 underline underline-offset-2 hover:text-white">{{ __('site.blog.clear_search') }}</a>
        </p>
    @endif

    @if ($tags->isNotEmpty())
        <nav class="reveal mb-10" aria-label="{{ __('site.blog.topics') }}">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $blogUrl }}" class="tag-pill {{ $activeTag ? '' : 'is-active' }}" @if (!$activeTag) aria-current="true" @endif>{{ __('site.blog.all') }}</a>
                @foreach ($visibleTags as $tag)
                    <a href="{{ $blogUrl }}?tag={{ urlencode($tag) }}" class="tag-pill {{ $activeTag === $tag ? 'is-active' : '' }}" @if ($activeTag === $tag) aria-current="true" @endif>{{ $tag }}<span class="tag-count">{{ $tagCounts[$tag] }}</span></a>
                @endforeach
            </div>
            @if ($moreTags->isNotEmpty())
                <details class="topics-more mt-3" @if ($moreOpen) open @endif>
                    <summary class="tag-pill cursor-pointer select-none">
                        <span class="topics-more-closed">{{ __('site.blog.more_topics') }} (+{{ $moreTags->count() }})</span>
                        <span class="topics-more-open">{{ __('site.blog.fewer_topics') }}</span>
                    </summary>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @foreach ($moreTags as $tag)
                            <a href="{{ $blogUrl }}?tag={{ urlencode($tag) }}" class="tag-pill {{ $activeTag === $tag ? 'is-active' : '' }}" @if ($activeTag === $tag) aria-current="true" @endif>{{ $tag }}<span class="tag-count">{{ $tagCounts[$tag] }}</span></a>
                        @endforeach
                    </div>
                </details>
            @endif
        </nav>
    @endif

    @if ($posts->isEmpty())
        <p class="glass rounded-3xl p-10 text-center text-slate-400">{{ filled($search) ? __('site.blog.no_results') : __('site.blog.empty') }}</p>
    @else
        @if ($featured)
            @include('blog._featured', ['post' => $featured])
        @endif

        @if ($items->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $post)
                    @include('blog._card', ['post' => $post, 'delay' => ($loop->index % 3) * 90, 'priority' => ! $featured && $loop->first && ! $posts->previousPageUrl()])
                @endforeach
            </div>
        @endif

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
</div>
@endsection
