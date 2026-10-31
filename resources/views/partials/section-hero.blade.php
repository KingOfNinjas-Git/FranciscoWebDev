@php
    $whatsapp = 'https://wa.me/' . preg_replace('/\D+/', '', config('portfolio.phone')) . '?text=' . rawurlencode(__('site.contact.whatsapp_text'));
@endphp

<section id="home" class="section-anchor relative flex min-h-screen items-center px-4 pb-16 pt-28">
    <div class="mx-auto grid w-full max-w-6xl items-center gap-12 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="space-y-6">
            <p class="eyebrow reveal">Francisco Cavaco, {{ strtolower(__('site.hero.role')) }}</p>
            <h1 class="reveal max-w-3xl text-4xl font-bold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl" style="--d:80ms">{{ __('site.hero.headline') }}</h1>
            <p class="reveal max-w-xl text-lg leading-8 text-slate-300" style="--d:160ms">{{ __('site.hero.reach') }}</p>

            <form id="quick-contact" action="#contact" method="get" class="reveal max-w-2xl" style="--d:240ms">
                <label for="quick-need" class="mb-2 block font-semibold text-white">{{ __('site.hero.quick_label') }}</label>
                <div class="quick-contact">
                    <input id="quick-need" name="need" type="text" maxlength="300" autocomplete="off" placeholder="{{ __('site.hero.quick_ph') }}">
                    <button type="submit" data-track="hero_contact" class="btn btn-primary px-7 py-3 text-sm font-semibold">{{ __('site.hero.cta_contact') }}</button>
                </div>
            </form>

            <div class="reveal flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-slate-300" style="--d:320ms">
                <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-400" aria-hidden="true"></span>{{ __('site.hero.available') }}</span>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" data-track="whatsapp" class="btn btn-ghost px-4 py-2 text-sm font-semibold">WhatsApp</a>
                <a href="#projects" data-nav data-track="hero_projects" class="btn btn-ghost px-4 py-2 text-sm font-semibold">{{ __('site.hero.cta_projects') }}</a>
            </div>
        </div>

        <div class="reveal relative mx-auto w-full max-w-xs lg:max-w-sm" style="--d:200ms">
            <div class="hero-frame glass rounded-[2rem] p-3">
                <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" fetchpriority="high" decoding="async" class="aspect-[4/5] w-full rounded-[1.5rem] object-cover">
            </div>
        </div>
    </div>
</section>
