<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me | Francisco Cavaco</title>
    <meta name="description" content="Learn about Francisco Cavaco, a computer science engineering student and web developer focused on accessible, maintainable web applications.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="About Me | Francisco Cavaco">
    <meta property="og:description" content="Meet Francisco Cavaco and learn about his web development experience, skills, and approach.">
    <meta name="twitter:card" content="summary">
    @vite(['resources/css/app.css'])
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-5xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <header class="mb-12 text-center md:mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">About</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white md:text-6xl">About me</h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-slate-300">
                I am a web developer focused on clear interfaces, maintainable code, and practical digital products.
            </p>
        </header>

        <section class="grid items-center gap-8 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="site-card overflow-hidden rounded-3xl border border-slate-800 p-3">
                <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="h-full w-full rounded-2xl object-cover md:max-h-[440px]">
            </div>

            <div class="space-y-6 text-lg leading-8 text-slate-300">
                <p>
                    I am a Computer Science Engineering student at Instituto Piaget. My work is focused on frontend development, accessibility, and web applications that are practical to maintain and simple to use.
                </p>
                <p>
                    I have completed a Higher Professional Technical Course in Game Development and Multimedia Applications, and I have also built experience in web development through internships and project work.
                </p>
                <p>
                    In recent work, I have built and improved projects using HTML, CSS, JavaScript, Laravel, PHP, React, and Tailwind. I focus on readable code, responsive layouts, and accessible interfaces that work well for the people using them.
                </p>
            </div>
        </section>

        <section class="mt-20">
            <div class="mb-8">
                <p class="text-xs font-semibold uppercase tracking-[0.24rem] text-slate-400">Skills</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Core tools and disciplines</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="site-card rounded-2xl border border-slate-800 p-5">
                    <h3 class="mb-3 text-lg font-semibold text-white">Frontend</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li>HTML</li>
                        <li>CSS</li>
                        <li>JavaScript</li>
                        <li>React</li>
                        <li>Tailwind</li>
                    </ul>
                </div>

                <div class="site-card rounded-2xl border border-slate-800 p-5">
                    <h3 class="mb-3 text-lg font-semibold text-white">Backend</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li>PHP</li>
                        <li>Laravel</li>
                        <li>MySQL</li>
                        <li>REST APIs</li>
                        <li>Blade templates</li>
                    </ul>
                </div>

                <div class="site-card rounded-2xl border border-slate-800 p-5">
                    <h3 class="mb-3 text-lg font-semibold text-white">Workflow</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li>Git</li>
                        <li>GitHub</li>
                        <li>Vite</li>
                        <li>Debugging</li>
                        <li>Deployment</li>
                    </ul>
                </div>

                <div class="site-card rounded-2xl border border-slate-800 p-5">
                    <h3 class="mb-3 text-lg font-semibold text-white">Focus</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li>Accessibility</li>
                        <li>Responsive design</li>
                        <li>UX clarity</li>
                        <li>Maintenance</li>
                        <li>Testing</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="mt-20 rounded-3xl border border-slate-800 bg-slate-900/70 p-6 md:p-8">
            <h2 class="text-2xl font-semibold text-white">Professional strengths</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-slate-300">Communicative and collaborative</div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-slate-300">Organised and detail-oriented</div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-slate-300">Focused on accessible product design</div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-slate-300">Comfortable working across frontend and backend tasks</div>
            </div>
        </section>
    </main>

    @include('partials.footer')

    <button id="scroll-to-top" class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/about.js'])
</body>
</html>
