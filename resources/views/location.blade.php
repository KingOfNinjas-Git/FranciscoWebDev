@extends('layouts.public', [
    'canonical' => \App\Http\Middleware\SetLocale::locationUrl($slug),
    'alternates' => collect(\App\Http\Middleware\SetLocale::LOCALES)->mapWithKeys(fn ($l) => [$l => \App\Http\Middleware\SetLocale::locationUrl($slug, $l)])->all(),
])

@section('title', __('site.location.meta_title', ['town' => $name]))
@section('description', __('site.location.meta_description', ['town' => $name]))

@push('head')
    @php
        $lead = __('site.location.lead', ['town' => $name]);
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => 'Francisco Cavaco | ' . __('site.hero.role'),
            'url' => \App\Http\Middleware\SetLocale::locationUrl($slug),
            'image' => asset('imgs/Francisco.jpg'),
            'description' => $lead,
            'provider' => ['@type' => 'Person', 'name' => 'Francisco Cavaco', 'url' => \App\Http\Middleware\SetLocale::homeUrl('en')],
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Seixal', 'addressCountry' => 'PT'],
            'areaServed' => ['@type' => 'City', 'name' => $name, 'containedInPlace' => ['@type' => 'Country', 'name' => 'Portugal']],
            'knowsLanguage' => ['pt', 'en', 'es'],
            'inLanguage' => app()->getLocale(),
        ];
        $crumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.location.home'), 'item' => \App\Http\Middleware\SetLocale::homeUrl()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('site.location.title', ['town' => $name]), 'item' => \App\Http\Middleware\SetLocale::locationUrl($slug)],
            ],
        ];
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($crumbs, $jsonFlags) !!}</script>
@endpush

@section('content')
@php $contact = \App\Http\Middleware\SetLocale::homeUrl() . '#contact'; @endphp
<article class="mx-auto max-w-5xl">
    <nav aria-label="Breadcrumb" class="reveal mb-6 text-sm text-slate-400">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ \App\Http\Middleware\SetLocale::homeUrl() }}" class="hover:text-white">{{ __('site.location.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="text-slate-200">{{ $name }}</li>
        </ol>
    </nav>

    <header class="reveal mb-12 max-w-3xl">
        <p class="eyebrow">{{ __('site.location.eyebrow', ['town' => $name]) }}</p>
        <h1 class="section-title">{{ __('site.location.title', ['town' => $name]) }}</h1>
        <p class="mt-4 text-lg leading-8 text-slate-300">{{ $lead }}</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ $contact }}" data-track="location_cta" class="btn btn-primary px-6 py-3 text-sm font-semibold">{{ __('site.location.cta_button') }}</a>
            <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('portfolio.phone')) }}?text={{ rawurlencode(__('site.contact.whatsapp_text')) }}" target="_blank" rel="noopener noreferrer" data-track="whatsapp" class="btn btn-ghost px-6 py-3 text-sm font-semibold">{{ __('site.contact.whatsapp') }}</a>
        </div>
    </header>

    {{-- The place --}}
    <section class="reveal glass mb-8 rounded-3xl p-6 md:p-8" aria-labelledby="about-town">
        <h2 id="about-town" class="text-2xl font-bold text-white">{{ __('site.location.about_title', ['town' => $name]) }}</h2>
        <p class="mt-4 leading-8 text-slate-300">{{ $blurb }}</p>
        <p class="mt-3 leading-8 text-slate-300">{{ $near }}</p>

        <h3 class="mt-8 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ __('site.location.businesses_title', ['town' => $name]) }}</h3>
        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($businesses as $kind)
                <li class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-slate-200">
                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-sky-300" aria-hidden="true"></span>{{ $kind }}
                </li>
            @endforeach
        </ul>

        @if ($places)
            <h3 class="mt-8 font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">{{ __('site.location.places_title', ['town' => $name]) }}</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($places as $place)
                    <span class="chip">{{ $place }}</span>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Why someone nearby --}}
    <section class="mb-12" aria-labelledby="why-near">
        <h2 id="why-near" class="reveal mb-5 text-2xl font-bold text-white">{{ __('site.location.why_title') }}</h2>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach (__('site.location.why') as $i => $item)
                <div class="reveal glass rounded-3xl p-6" style="--d:{{ $i * 90 }}ms">
                    <h3 class="text-lg font-semibold text-white">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- What I can build --}}
    <section class="mb-12" aria-labelledby="town-services">
        <h2 id="town-services" class="reveal mb-5 text-2xl font-bold text-white">{{ __('site.location.services_title') }}</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach (__('site.services.items') as $i => $item)
                <div class="reveal glass rounded-3xl p-6" style="--d:{{ $i * 90 }}ms">
                    <h3 class="text-lg font-semibold text-white">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section class="mb-12" aria-labelledby="town-process">
        <h2 id="town-process" class="reveal mb-5 text-2xl font-bold text-white">{{ __('site.location.process_title') }}</h2>
        <ol class="grid gap-5 md:grid-cols-4">
            @foreach (__('site.process.steps') as $i => $step)
                <li class="reveal glass rounded-3xl p-5" style="--d:{{ $i * 90 }}ms">
                    <span class="font-mono text-xs text-sky-300">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-2 font-semibold text-white">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-300">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Questions --}}
    <section class="mb-12" aria-labelledby="town-faq">
        <h2 id="town-faq" class="reveal mb-5 text-2xl font-bold text-white">{{ __('site.location.faq_title', ['town' => $name]) }}</h2>
        <div class="space-y-3">
            @foreach (__('site.faq.items') as $item)
                <details class="faq-item reveal glass rounded-2xl p-5">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 font-semibold text-white">
                        <span>{{ $item['q'] }}</span>
                        <svg class="faq-chevron h-5 w-5 shrink-0 text-sky-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 leading-7 text-slate-300">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- Ask --}}
    <aside class="reveal glass rounded-3xl p-6 text-center md:p-10" aria-labelledby="town-cta">
        <h2 id="town-cta" class="text-2xl font-bold text-white">{{ __('site.location.cta_title', ['town' => $name]) }}</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-300">{{ __('site.location.cta_text') }}</p>
        <div class="mt-6">
            <a href="{{ $contact }}" data-track="location_cta" class="btn btn-primary px-7 py-3 text-sm font-semibold">{{ __('site.location.cta_button') }}</a>
        </div>
    </aside>

    {{-- The other towns --}}
    <nav class="mt-12 text-center text-sm text-slate-400" aria-label="{{ __('site.location.other_areas') }}">
        <p>{{ __('site.location.other_areas') }}</p>
        <ul class="mt-3 flex flex-wrap justify-center gap-2">
            @foreach ($others as $otherSlug => $otherName)
                <li><a href="{{ \App\Http\Middleware\SetLocale::locationUrl($otherSlug) }}" class="chip hover:border-sky-400">{{ $otherName }}</a></li>
            @endforeach
        </ul>
    </nav>
</article>
@endsection
