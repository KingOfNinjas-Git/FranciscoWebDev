<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Francisco Cavaco | Web Developer</title>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-6xl px-4 pb-20 pt-12 md:pt-16">
        <section class="grid items-center gap-10 md:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
                <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Web developer</p>
                <h1 class="animate-fade-in-up text-4xl font-semibold tracking-tight text-white md:text-6xl">
                    Francisco Cavaco
                </h1>
                <p class="max-w-xl text-lg leading-8 text-slate-300">
                    I build accessible, dependable web applications with a focus on clear UX, clean code, and business-ready results.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('projects') }}" class="site-button site-button-primary px-6 py-3 text-sm font-medium text-white">View projects</a>
                    <a href="{{ route('contact') }}" class="site-button px-6 py-3 text-sm font-medium text-slate-200">Contact</a>
                </div>
            </div>

            <div class="site-card overflow-hidden rounded-3xl border border-slate-800 p-3">
                <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="h-full w-full rounded-2xl object-cover md:max-h-[420px]">
            </div>
        </section>

        <section class="mt-20">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24rem] text-slate-400">Technologies</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Current stack</h2>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18rem] text-slate-400">Frontend</h3>
                    <div class="flex flex-wrap gap-3">
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">HTML</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">CSS</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">JavaScript</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">React</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">Tailwind</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-800 pt-4">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18rem] text-slate-400">Backend &amp; Data</h3>
                    <div class="flex flex-wrap gap-3">
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">PHP</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">Laravel</span>
                        </div>
                        <div class="tech-chip rounded-2xl">
                            <span class="chip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" /></svg>
                            </span>
                            <span class="chip-label text-sm font-medium text-slate-200">MySQL</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-20 rounded-3xl border border-slate-800 bg-slate-900/70 p-6 md:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.24rem] text-slate-400">Focus</p>
            <h2 class="mt-2 text-2xl font-semibold text-white">What I work on</h2>
            <ul class="mt-6 grid gap-4 text-slate-300 md:grid-cols-2">
                <li class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">Laravel and PHP application work</li>
                <li class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">Frontend interfaces built with React and Tailwind</li>
                <li class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">Accessible UI and content structure</li>
                <li class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">Production-ready code with clear maintenance in mind</li>
            </ul>
        </section>
    </main>

    @include('partials.footer')
    <button id="scroll-to-top" class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/home.js'])
</body>
</html>