<header class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-sm">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-sm font-semibold text-sky-300">
                FC
            </span>
            <span class="text-lg font-semibold text-slate-100">Francisco</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="text-sm font-medium text-slate-300 transition hover:text-slate-100">Home</a>
            <a href="{{ route('about') }}" class="text-sm font-medium text-slate-300 transition hover:text-slate-100">About</a>
            <a href="{{ route('projects') }}" class="text-sm font-medium text-slate-300 transition hover:text-slate-100">Projects</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium text-slate-300 transition hover:text-slate-100">Contact</a>
            @if(session('is_admin'))
                <a href="{{ route('admin.projects.index') }}" class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm font-medium text-slate-100 transition hover:border-slate-500 hover:bg-slate-800">Admin</a>
            @endif
        </nav>

        <button id="mobile-menu-btn" class="inline-flex rounded-lg border border-slate-700 p-2 text-slate-300 md:hidden" type="button" aria-label="Open menu" aria-expanded="false">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu" class="max-h-0 overflow-hidden border-t border-transparent bg-slate-950 opacity-0 transition-all duration-300 md:hidden">
        <ul class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-4">
            <li><a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-slate-100">Home</a></li>
            <li><a href="{{ route('about') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-slate-100">About</a></li>
            <li><a href="{{ route('projects') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-slate-100">Projects</a></li>
            <li><a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-slate-100">Contact</a></li>
            @if(session('is_admin'))
                <li><a href="{{ route('admin.projects.index') }}" class="mt-1 block rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100">Admin</a></li>
            @endif
        </ul>
    </div>
</header>

@include('partials.lang_splash')
