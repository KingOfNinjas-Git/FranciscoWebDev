@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::labUrl(),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::labUrl($l)])->all(),
])

@section('title', __('site.lab.meta_title'))
@section('description', __('site.lab.meta_description'))

@section('content')
<div class="mx-auto max-w-6xl">
    <header class="reveal mb-10 max-w-3xl">
        <p class="eyebrow">{{ __('site.lab.eyebrow') }}</p>
        <h1 class="section-title">{{ __('site.lab.title') }}</h1>
        <p class="mt-3 text-slate-400">{{ __('site.lab.intro') }}</p>
    </header>

    @if ($demos->isEmpty())
        <p class="glass rounded-3xl p-10 text-center text-slate-400">{{ __('site.lab.empty') }}</p>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($demos as $demo)
                @php
                    $state = $status[$demo->live_url] ?? ['up' => false, 'ms' => null, 'at' => time()];
                    $minutes = (int) floor((time() - $state['at']) / 60);
                    $cover = $demo->images[0] ?? null;
                @endphp
                <article class="reveal glass flex flex-col overflow-hidden rounded-3xl" style="--d: {{ ($loop->index % 3) * 90 }}ms">
                    @if ($cover)
                        <div class="project-media">
                            <img src="{{ $cover['thumb'] ?? $cover['url'] }}" @if ($cover['srcset']) srcset="{{ $cover['srcset'] }}" sizes="(min-width: 1024px) 380px, (min-width: 768px) 45vw, 92vw" @endif alt="{{ $cover['alt'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async" class="h-full w-full object-cover">
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-5">
                        <p class="flex items-center gap-2 text-xs font-semibold {{ $state['up'] ? 'text-emerald-300' : 'text-amber-300' }}" role="status">
                            <span class="inline-block h-2.5 w-2.5 rounded-full {{ $state['up'] ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]' : 'bg-amber-400' }}" aria-hidden="true"></span>
                            <span class="sr-only">{{ __('site.lab.status_label') }}:</span>
                            {{ $state['up'] ? __('site.lab.online') : __('site.lab.offline') }}
                            <span class="font-normal text-slate-400">&middot; {{ $minutes < 1 ? __('site.lab.checked_now') : __('site.lab.checked', ['min' => $minutes]) }}</span>
                        </p>
                        <h2 class="mt-3 text-xl font-semibold text-white">{{ $demo->title }}</h2>
                        @if ($demo->description)
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-300">{{ $demo->description }}</p>
                        @endif
                        @if ($demo->tags)
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach (array_slice($demo->tags, 0, 4) as $tag)
                                    <span class="chip">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-auto flex flex-wrap gap-3 pt-6">
                            <a href="{{ $demo->live_url }}" data-track="lab_live" target="_blank" rel="noopener noreferrer" class="btn btn-primary px-5 py-2.5 text-sm font-semibold">{{ __('site.lab.open') }} &nearr;</a>
                            <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($demo->slug) }}" class="btn btn-ghost px-5 py-2.5 text-sm font-medium">{{ __('site.lab.case_study') }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="mt-8 text-center text-sm text-slate-400">{{ __('site.lab.note') }}</p>
    @endif
</div>
@endsection
