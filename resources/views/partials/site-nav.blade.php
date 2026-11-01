@php
    use App\Http\Middleware\SetLocale;

    // On the one-page home the links are in-page anchors; on other pages (blog) they lead back to the home page sections
    $onHome = request()->routeIs('home', 'home.pt', 'home.es');
    $base = $onHome ? '' : SetLocale::homeUrl();
    $onBlog = request()->is('blog', 'blog/*', 'pt/blog', 'pt/blog/*', 'es/blog', 'es/blog/*');

    $links = [
        'home' => $base . '#home',
        'projects' => $base . '#projects',
        'services' => $base . '#services',
        'about' => $base . '#about',
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

<header class="site-header">
    <div class="site-header-bar">
        <a href="{{ $links['home'] }}" @if ($onHome) data-nav @endif class="brand">Francisco Cavaco</a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Main navigation">
            @foreach ($links as $key => $href)
                @continue($key === 'home')
                <a href="{{ $href }}" @if ($onHome) data-nav @endif class="nav-link px-4 py-2 font-medium text-slate-300">{{ __('site.nav.' . $key) }}</a>
            @endforeach
            <a href="{{ SetLocale::blogUrl() }}" @if ($onBlog) aria-current="page" @endif class="nav-link px-4 py-2 font-medium {{ $onBlog ? 'is-active' : 'text-slate-300' }}">{{ __('site.nav.blog') }}</a>
            @if (session('is_admin'))
                <a href="{{ route('admin.projects.index') }}" class="nav-link px-4 py-2 font-medium text-sky-300">{{ __('site.nav.admin') }}</a>
            @endif
        </nav>

        <div class="flex items-center gap-2">
            <div class="lang-switch flex items-center" role="group" aria-label="{{ __('site.nav.language') }}">
                @foreach ($locales as $code => $label)
                    <a href="{{ $langHref($code) }}" data-lang hreflang="{{ $code }}" lang="{{ $code }}"
                       @if (app()->getLocale() === $code) aria-current="true" @endif
                       class="lang-btn rounded-full px-3 py-1.5 text-sm font-bold {{ app()->getLocale() === $code ? 'is-active' : 'text-slate-300' }}">{{ $label }}</a>
                @endforeach
            </div>

            <button id="mobile-menu-btn" class="glass-btn inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-200 md:hidden" type="button" aria-label="{{ __('site.nav.menu') }}" aria-controls="mobile-menu" aria-expanded="false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <nav id="mobile-menu" aria-label="Mobile navigation" aria-hidden="true" inert class="md:hidden">
        <ul class="flex flex-col gap-1 p-3">
            @foreach ($links as $key => $href)
                @continue($key === 'home')
                <li><a href="{{ $href }}" @if ($onHome) data-nav @endif class="block rounded-2xl px-4 py-3 font-medium text-slate-200">{{ __('site.nav.' . $key) }}</a></li>
            @endforeach
            <li><a href="{{ SetLocale::blogUrl() }}" class="block rounded-2xl px-4 py-3 font-medium text-slate-200">{{ __('site.nav.blog') }}</a></li>
            @if (session('is_admin'))
                <li><a href="{{ route('admin.projects.index') }}" class="block rounded-2xl px-4 py-3 font-medium text-sky-300">{{ __('site.nav.admin') }}</a></li>
            @endif
        </ul>
    </nav>
</header>
