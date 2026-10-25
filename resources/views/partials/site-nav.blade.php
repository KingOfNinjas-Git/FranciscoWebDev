@php
    use App\Http\Middleware\SetLocale;

    // On the one-page home the links are in-page anchors; on other pages (blog) they lead back to the home page sections
    $onHome = request()->routeIs('home', 'home.pt', 'home.es');
    $base = $onHome ? '' : SetLocale::homeUrl();
    $onBlog = request()->is('blog', 'blog/*', 'pt/blog', 'pt/blog/*', 'es/blog', 'es/blog/*');

    $links = [
        'home' => $base . '#home',
        'about' => $base . '#about',
        'projects' => $base . '#projects',
        'contact' => $base . '#contact',
    ];
    $locales = ['en' => 'EN', 'pt' => 'PT', 'es' => 'ES'];

    // Pages can pass $langUrls (code => URL of the same page in that language) to keep visitors on the page when switching
    $langUrls = $langUrls ?? [];
    $langHref = function (string $code) use ($langUrls) {
        $to = isset($langUrls[$code]) ? parse_url($langUrls[$code], PHP_URL_PATH) : null;

        return route('lang.switch', array_filter(['locale' => $code, 'to' => $to]));
    };
@endphp

<header class="fixed inset-x-0 top-4 z-50 px-4">
    <div class="glass glass-nav mx-auto flex max-w-5xl items-center justify-between rounded-full py-2 pl-3 pr-2 md:pl-4">
        <a href="{{ $links['home'] }}" @if ($onHome) data-nav @endif class="flex items-center gap-3" aria-label="Francisco Cavaco">
            <span class="logo-mark flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold text-white">FC</span>
            <span class="hidden text-sm font-semibold tracking-wide text-slate-100 sm:inline">Francisco</span>
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Main navigation">
            @foreach ($links as $key => $href)
                <a href="{{ $href }}" @if ($onHome) data-nav @endif class="nav-link rounded-full px-4 py-2 text-sm font-medium text-slate-300 transition hover:text-white">{{ __('site.nav.' . $key) }}</a>
            @endforeach
            <a href="{{ SetLocale::blogUrl() }}" @if ($onBlog) aria-current="page" @endif class="nav-link rounded-full px-4 py-2 text-sm font-medium transition {{ $onBlog ? 'is-active' : 'text-slate-300 hover:text-white' }}">{{ __('site.nav.blog') }}</a>
            @if (session('is_admin'))
                <a href="{{ route('admin.projects.index') }}" class="nav-link rounded-full px-4 py-2 text-sm font-medium text-sky-300 hover:text-white">{{ __('site.nav.admin') }}</a>
            @endif
        </nav>

        <div class="flex items-center gap-2">
            <div class="lang-switch flex items-center rounded-full p-1" role="group" aria-label="{{ __('site.nav.language') }}">
                @foreach ($locales as $code => $label)
                    <a href="{{ $langHref($code) }}" data-lang hreflang="{{ $code }}" lang="{{ $code }}"
                       @if (app()->getLocale() === $code) aria-current="true" @endif
                       class="lang-btn rounded-full px-2.5 py-1 text-xs font-semibold {{ app()->getLocale() === $code ? 'is-active' : 'text-slate-400 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </div>

            <button id="mobile-menu-btn" class="glass-btn inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-200 md:hidden" type="button" aria-label="{{ __('site.nav.menu') }}" aria-controls="mobile-menu" aria-expanded="false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <nav id="mobile-menu" aria-label="Mobile navigation" aria-hidden="true" inert class="glass mx-auto mt-2 max-w-5xl overflow-hidden rounded-3xl md:hidden">
        <ul class="flex flex-col gap-1 p-3">
            @foreach ($links as $key => $href)
                <li><a href="{{ $href }}" @if ($onHome) data-nav @endif class="block rounded-2xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-white/10">{{ __('site.nav.' . $key) }}</a></li>
            @endforeach
            <li><a href="{{ SetLocale::blogUrl() }}" class="block rounded-2xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-white/10">{{ __('site.nav.blog') }}</a></li>
            @if (session('is_admin'))
                <li><a href="{{ route('admin.projects.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-medium text-sky-300 hover:bg-white/10">{{ __('site.nav.admin') }}</a></li>
            @endif
        </ul>
    </nav>
</header>
