<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Francisco Cavaco</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-5xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <header class="mb-12 text-center md:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Contact</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white md:text-6xl">Let?s talk</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-300">
                If you need a web developer for a project, a maintenance task, or a new interface, feel free to get in touch.
            </p>
        </header>

        <section class="grid gap-5 md:grid-cols-3">
            <a href="mailto:franciscomfcavaco@gmail.com" class="site-card rounded-2xl border border-slate-800 p-5 transition hover:border-slate-600">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v9A2.5 2.5 0 0117.5 19h-11A2.5 2.5 0 014 16.5v-9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8l8 6 8-6" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.2rem] text-slate-400">Email</p>
                <p class="mt-3 text-base text-slate-100">franciscomfcavaco@gmail.com</p>
            </a>

            <a href="tel:+351924490605" class="site-card rounded-2xl border border-slate-800 p-5 transition hover:border-slate-600">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 5.5A2.5 2.5 0 016 3h2.3a1.5 1.5 0 011.42 1.05l1.03 3.3a1.5 1.5 0 01-.75 1.73l-1.77 1.06a11.7 11.7 0 005.74 5.74l1.06-1.77a1.5 1.5 0 011.73-.75l3.3 1.03A1.5 1.5 0 0121 16.7V19a2.5 2.5 0 01-2.5 2.5H18A15.5 15.5 0 013.5 5.5z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.2rem] text-slate-400">Phone</p>
                <p class="mt-3 text-base text-slate-100">+351 924 490 605</p>
            </a>

            <div class="site-card rounded-2xl border border-slate-800 p-5">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-6.5-4.35-9-8.5A5.5 5.5 0 0112 5.5a5.5 5.5 0 019 7z" />
                        <circle cx="12" cy="11" r="2.5" />
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
