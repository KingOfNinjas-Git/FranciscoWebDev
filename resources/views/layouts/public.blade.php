<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $ogLocales = ['en' => 'en_US', 'pt' => 'pt_PT', 'es' => 'es_ES'];
        $ogImage = $ogImage ?? asset('imgs/og-image.png');
    @endphp
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')">
    <meta name="theme-color" content="#020817">
    @if (!empty($noindex))
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    @foreach ($alternates as $code => $url)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $url }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates['en'] }}">
    <link rel="alternate" type="application/rss+xml" title="Francisco Cavaco | Blog" href="{{ route('blog.feed') }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="Francisco Cavaco">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title')">
    <meta name="twitter:description" content="@yield('description')">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @stack('head')
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/site.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <div class="bg-fx" aria-hidden="true">
        <span class="bg-orb bg-orb-a"></span>
        <span class="bg-orb bg-orb-b"></span>
        <span class="bg-grid"></span>
    </div>

    @include('partials.site-nav', ['langUrls' => $alternates])

    <main class="px-4 pb-24 pt-32">
        @yield('content')
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" type="button" aria-label="{{ __('site.nav.top') }}" class="glass fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full text-slate-100 transition hover:-translate-y-0.5">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>
</body>
</html>
