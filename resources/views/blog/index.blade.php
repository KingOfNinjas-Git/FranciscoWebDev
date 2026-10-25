@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::blogUrl(),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::blogUrl($l)])->all(),
    'noindex' => filled($search ?? null),
])

@section('title', __('site.blog.meta_title'))
@section('description', __('site.blog.meta_description'))

@section('content')
<div class="mx-auto max-w-6xl">
    <header class="reveal mb-10">
        <p class="eyebrow">{{ __('site.blog.eyebrow') }}</p>
        <h1 class="section-title">{{ __('site.blog.title') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.blog.intro') }}</p>
    </header>

    <form method="GET" action="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" role="search" class="reveal mb-6 flex max-w-xl gap-2">
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
            &middot; <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}{{ $activeTag ? '?tag=' . urlencode($activeTag) : '' }}" class="text-sky-300 underline underline-offset-2 hover:text-white">{{ __('site.blog.clear_search') }}</a>
        </p>
    @endif

    @if ($tags->isNotEmpty())
        <nav class="reveal mb-10 flex flex-wrap items-center gap-2" aria-label="{{ __('site.blog.topics') }}">
            <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="tag-pill {{ $activeTag ? '' : 'is-active' }}" @if (!$activeTag) aria-current="true" @endif>{{ __('site.blog.all') }}</a>
            @foreach ($tags as $tag)
                <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}?tag={{ urlencode($tag) }}" class="tag-pill {{ $activeTag === $tag ? 'is-active' : '' }}" @if ($activeTag === $tag) aria-current="true" @endif>{{ $tag }}</a>
            @endforeach
        </nav>
    @endif

    @if ($posts->isEmpty())
        <p class="glass rounded-3xl p-10 text-center text-slate-400">{{ filled($search) ? __('site.blog.no_results') : __('site.blog.empty') }}</p>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                @include('blog._card', ['post' => $post, 'delay' => ($loop->index % 3) * 90])
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

    <p class="mt-12 text-center text-sm text-slate-400"><a href="{{ route('blog.feed') }}" class="hover:text-slate-300">{{ __('site.blog.rss') }}</a></p>
</div>
@endsection
