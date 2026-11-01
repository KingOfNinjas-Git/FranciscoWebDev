<footer class="px-4 pb-10 pt-6" style="background:#fff">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 border-t-2 border-slate-300/20 pt-8 text-center text-slate-400 md:flex-row">
        <p>&copy; {{ date('Y') }} Francisco Cavaco. {{ __('site.footer.rights') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 font-medium">
            <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="hover:text-white hover:underline">{{ __('site.nav.blog') }}</a>
            <a href="{{ \App\Http\Middleware\SetLocale::labUrl() }}" class="hover:text-white hover:underline">{{ __('site.footer.lab') }}</a>
            <a href="{{ \App\Http\Middleware\SetLocale::privacyUrl() }}" class="hover:text-white hover:underline">{{ __('site.footer.privacy') }}</a>
            <a href="{{ \App\Http\Middleware\SetLocale::accessibilityUrl() }}" class="hover:text-white hover:underline">{{ __('site.footer.accessibility') }}</a>
            <a href="mailto:{{ config('portfolio.contact_email') }}" class="hover:text-white hover:underline">Email</a>
            <a href="{{ config('portfolio.linkedin') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline">LinkedIn</a>
            <a href="{{ config('portfolio.github') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white hover:underline">GitHub</a>
        </div>
    </div>
    <nav class="mx-auto mt-5 max-w-6xl text-center text-sm text-slate-400" aria-label="{{ __('site.footer.areas') }}">
        <span class="mr-2">{{ __('site.footer.areas') }}:</span>
        @foreach (config('locations') as $townSlug => $town)
            <a href="{{ \App\Http\Middleware\SetLocale::locationUrl($townSlug) }}" class="mx-1.5 inline-block hover:text-white hover:underline">{{ $town['name'] }}</a>
        @endforeach
    </nav>
</footer>
