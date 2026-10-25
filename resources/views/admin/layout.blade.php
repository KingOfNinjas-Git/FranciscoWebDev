<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') | Francisco Cavaco</title>
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <a href="#main" class="skip-link">Skip to main content</a>
    <div class="bg-fx" aria-hidden="true">
        <span class="bg-orb bg-orb-a"></span>
        <span class="bg-orb bg-orb-b"></span>
        <span class="bg-grid"></span>
    </div>

    @hasSection('bare')
        @yield('bare')
    @else
        <header class="px-4 pt-4">
            <div class="glass glass-nav mx-auto flex max-w-6xl items-center justify-between rounded-full py-2 pl-3 pr-2">
                <a href="{{ route('admin.projects.index') }}" class="flex items-center gap-3">
                    <span class="logo-mark flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold text-white">FC</span>
                    <span class="hidden text-sm font-semibold text-slate-100 sm:inline">Admin</span>
                </a>
                <nav class="flex items-center gap-1" aria-label="Admin navigation">
                    <a href="{{ route('admin.projects.index') }}" class="nav-link rounded-full px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.projects.*') ? 'is-active' : 'text-slate-300 hover:text-white' }}">Projects</a>
                    <a href="{{ route('admin.posts.index') }}" class="nav-link rounded-full px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.posts.*') ? 'is-active' : 'text-slate-300 hover:text-white' }}">Blog</a>
                    <a href="{{ route('home') }}" target="_blank" class="nav-link rounded-full px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">View site &nearr;</a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-ghost px-4 py-2 text-sm font-medium">Logout</button>
                    </form>
                </nav>
            </div>
        </header>

        <main id="main" tabindex="-1" class="mx-auto max-w-6xl px-4 pb-20 pt-10">
            @if (session('success'))
                <div role="status" class="mb-6 rounded-2xl border border-emerald-400/40 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <ul role="alert" class="mb-6 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @yield('content')
        </main>
    @endif
</body>
</html>
