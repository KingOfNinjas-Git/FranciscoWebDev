<footer class="relative border-t border-white/10 px-4 py-8">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 text-center text-sm text-slate-400 md:flex-row">
        <p>&copy; {{ date('Y') }} Francisco Cavaco. {{ __('site.footer.rights') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-5">
            <a href="{{ \App\Http\Middleware\SetLocale::blogUrl() }}" class="transition hover:text-white">{{ __('site.nav.blog') }}</a>
            <a href="mailto:{{ config('portfolio.contact_email') }}" class="transition hover:text-white">Email</a>
            <a href="{{ config('portfolio.linkedin') }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">LinkedIn</a>
            <a href="{{ config('portfolio.github') }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">GitHub</a>
        </div>
    </div>
</footer>
