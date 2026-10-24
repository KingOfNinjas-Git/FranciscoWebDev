<section id="home" class="section-anchor relative flex min-h-screen items-center px-4 pb-16 pt-28">
    <div class="mx-auto grid w-full max-w-6xl items-center gap-12 md:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <p class="eyebrow reveal">{{ __('site.hero.eyebrow') }}</p>
            <h1 class="reveal text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-7xl" style="--d:80ms">
                <span class="block text-2xl font-medium text-slate-300 sm:text-3xl">{{ __('site.hero.greeting') }}</span>
                <span class="text-gradient">Francisco Cavaco</span>
            </h1>
            <p class="reveal font-mono text-lg text-sky-300 md:text-xl" style="--d:160ms">
                <span aria-hidden="true">&gt; </span>{{ __('site.hero.role') }}<span class="caret" aria-hidden="true"></span>
            </p>
            <p class="reveal max-w-xl text-lg leading-8 text-slate-300" style="--d:240ms">{{ __('site.hero.text') }}</p>
            <div class="reveal flex flex-col gap-3 sm:flex-row" style="--d:320ms">
                <a href="#projects" data-nav class="btn btn-primary px-6 py-3 text-sm font-semibold">{{ __('site.hero.cta_projects') }}</a>
                <a href="#contact" data-nav class="btn btn-ghost px-6 py-3 text-sm font-semibold">{{ __('site.hero.cta_contact') }}</a>
            </div>
        </div>

        <div class="reveal relative mx-auto w-full max-w-sm" style="--d:200ms">
            <div class="hero-frame glass rounded-[2rem] p-3">
                <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="aspect-[4/5] w-full rounded-[1.5rem] object-cover">
            </div>
            <span class="hero-chip hero-chip-a glass" aria-hidden="true">Laravel</span>
            <span class="hero-chip hero-chip-b glass" aria-hidden="true">React</span>
            <span class="hero-chip hero-chip-c glass" aria-hidden="true">Tailwind</span>
        </div>
    </div>

    <a href="#about" data-nav class="scroll-cue" aria-label="{{ __('site.hero.scroll') }}">
        <span>{{ __('site.hero.scroll') }}</span>
        <span class="scroll-cue-line" aria-hidden="true"></span>
    </a>
</section>
