<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.meta.title') }}</title>
    <meta name="description" content="{{ __('site.meta.description') }}">
    @php
        $locale = app()->getLocale();
        $canonical = \App\Http\Middleware\SetLocale::homeUrl($locale);
        $ogLocales = ['en' => 'en_US', 'pt' => 'pt_PT', 'es' => 'es_ES'];
    @endphp
    <meta name="theme-color" content="#020817">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach ($ogLocales as $code => $ogCode)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ \App\Http\Middleware\SetLocale::homeUrl($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ \App\Http\Middleware\SetLocale::homeUrl('en') }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Francisco Cavaco">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ __('site.meta.title') }}">
    <meta property="og:description" content="{{ __('site.meta.description') }}">
    <meta property="og:locale" content="{{ $ogLocales[$locale] }}">
    @foreach ($ogLocales as $code => $ogCode)
        @if ($code !== $locale)
            <meta property="og:locale:alternate" content="{{ $ogCode }}">
        @endif
    @endforeach
    <meta property="og:image" content="{{ asset('imgs/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('site.meta.title') }}">
    <meta name="twitter:description" content="{{ __('site.meta.description') }}">
    <meta name="twitter:image" content="{{ asset('imgs/og-image.png') }}">
    @php
        $personSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => 'Francisco Cavaco',
            'jobTitle' => __('site.hero.role'),
            'url' => $canonical,
            'image' => asset('imgs/Francisco.jpg'),
            'email' => 'mailto:' . config('portfolio.contact_email'),
            'sameAs' => [config('portfolio.linkedin'), config('portfolio.github')],
            'alumniOf' => 'Instituto Piaget',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/site.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <a href="#main" class="skip-link">{{ __('site.nav.skip') }}</a>
    <div class="bg-fx" aria-hidden="true">
        <span class="bg-orb bg-orb-a"></span>
        <span class="bg-orb bg-orb-b"></span>
        <span class="bg-grid"></span>
    </div>

    @include('partials.site-nav')

    <main id="main" tabindex="-1">
        @include('partials.section-hero')
        @include('partials.section-about')
        @include('partials.section-projects')
        @include('partials.section-blog')
        @include('partials.section-contact')
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" type="button" aria-label="{{ __('site.nav.top') }}" class="glass fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full text-slate-100 transition hover:-translate-y-0.5">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <script type="application/json" id="projects-data">@json($projects)</script>
</body>
</html>
