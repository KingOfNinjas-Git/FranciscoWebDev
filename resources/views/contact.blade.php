<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Francisco Cavaco</title>
    <meta name="description" content="Contact Francisco Cavaco about web development projects, maintenance work, and frontend or backend opportunities.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Contact | Francisco Cavaco">
    <meta property="og:description" content="Get in touch with Francisco Cavaco about web development projects and opportunities.">
    <meta name="twitter:card" content="summary">
    @vite(['resources/css/app.css'])
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-5xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <header class="mb-12 text-center md:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Contact</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white md:text-6xl">Let's talk</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-300">
                If you need a web developer for a project, a maintenance task, or a new interface, feel free to get in touch.
            </p>
        </header>

        <section class="grid gap-5 md:grid-cols-3">
            <a href="mailto:franciscomfcavaco@gmail.com" class="site-card rounded-2xl border border-slate-800 p-5 transition hover:border-slate-600">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z"/>
                        <path d="M3.75 7.5 12 13.5l8.25-6"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.2rem] text-slate-400">Email</p>
                <p class="mt-3 text-base text-slate-100">franciscomfcavaco@gmail.com</p>
            </a>

            <a href="tel:+351924490605" class="site-card rounded-2xl border border-slate-800 p-5 transition hover:border-slate-600">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h1.15a1.5 1.5 0 0 1 1.46 1.14l.58 2.44a1.5 1.5 0 0 1-.72 1.63l-1.32.73a13.14 13.14 0 0 0 6.46 6.46l.73-1.32a1.5 1.5 0 0 1 1.63-.72l2.44.58a1.5 1.5 0 0 1 1.14 1.46v1.15A2.25 2.25 0 0 1 17.25 21h-.75A16.5 16.5 0 0 1 2.25 7.5v-.75Z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.2rem] text-slate-400">Phone</p>
                <p class="mt-3 text-base text-slate-100">+351 924 490 605</p>
            </a>

            <div class="site-card rounded-2xl border border-slate-800 p-5">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 11.25A3 3 0 1 1 9 11.25A3 3 0 0 1 15 11.25Z"/>
                        <path d="M19.5 10.5c0 5.25-7.5 10.5-7.5 10.5S4.5 15.75 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.2rem] text-slate-400">Location</p>
                <p class="mt-3 text-base text-slate-100">Portugal</p>
            </div>
        </section>

        <section class="mt-12 rounded-3xl border border-slate-800 bg-slate-900/70 p-6 md:p-8">
            <h2 class="text-2xl font-semibold text-white">Social</h2>
            <div class="mt-5 flex flex-wrap gap-4">
                <a href="https://www.linkedin.com/in/francisco-cavaco-dev" target="_blank" class="rounded-full border border-slate-700 bg-slate-950 px-4 py-2 text-sm text-slate-200 transition hover:border-slate-500 hover:text-white">LinkedIn</a>
                <a href="https://github.com/FranciscoWebDev" target="_blank" class="rounded-full border border-slate-700 bg-slate-950 px-4 py-2 text-sm text-slate-200 transition hover:border-slate-500 hover:text-white">GitHub</a>
            </div>
        </section>
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js'])
</body>
</html>
