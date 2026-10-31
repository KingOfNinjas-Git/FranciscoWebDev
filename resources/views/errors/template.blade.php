<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ __("site.errors.$code.title") }} | Francisco Cavaco</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/css/site.css'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <div class="bg-fx" aria-hidden="true">
        <span class="bg-orb bg-orb-a"></span>
    </div>

    {{-- Self-contained on purpose: an error page must not depend on the database, session or routes --}}
    <main id="main" class="flex min-h-screen items-center justify-center px-4 py-16">
        <div class="glass w-full max-w-xl rounded-[2rem] p-8 text-center md:p-12">
            <a href="{{ url('/') }}" class="logo-mark mx-auto mb-8 flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white" aria-label="Francisco Cavaco">FC</a>
            <p class="text-gradient font-mono text-7xl font-bold md:text-8xl" aria-hidden="true">{{ $code }}</p>
            <h1 class="mt-4 text-2xl font-bold text-white md:text-3xl">{{ __("site.errors.$code.title") }}</h1>
            <p class="mx-auto mt-3 max-w-md text-slate-300">{{ __("site.errors.$code.text") }}</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ url('/') }}" class="btn btn-primary px-6 py-3 text-sm font-semibold">{{ __('site.errors.home') }}</a>
                <a href="{{ url('/blog') }}" class="btn btn-ghost px-6 py-3 text-sm font-semibold">{{ __('site.errors.blog') }}</a>
            </div>
        </div>
    </main>
</body>
</html>
