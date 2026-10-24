<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.meta.title') }}</title>
    <meta name="description" content="{{ __('site.meta.description') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('site.meta.title') }}">
    <meta property="og:description" content="{{ __('site.meta.description') }}">
    <meta name="twitter:card" content="summary">
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/site.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <div class="bg-fx" aria-hidden="true">
        <span class="bg-orb bg-orb-a"></span>
        <span class="bg-orb bg-orb-b"></span>
        <span class="bg-grid"></span>
    </div>

    @include('partials.site-nav')

    <main>
        @include('partials.section-hero')
        @include('partials.section-about')
        @include('partials.section-projects')
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
