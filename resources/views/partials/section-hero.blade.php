@php
    $whatsapp = 'https://wa.me/' . preg_replace('/\D+/', '', config('portfolio.phone')) . '?text=' . rawurlencode(__('site.contact.whatsapp_text'));
@endphp

<section id="home" class="section-anchor hero">
    <div class="hero-grid">
        <div>
            <p class="hero-where">Francisco Cavaco, {{ strtolower(__('site.hero.role')) }}</p>
            <h1>{{ __('site.hero.headline') }}</h1>
            <p class="hero-reach">{{ __('site.hero.reach') }}</p>

            <form id="quick-contact" action="#contact" method="get" class="hero-card">
                <label for="quick-need">{{ __('site.hero.quick_label') }}</label>
                <div class="quick-contact">
                    <input id="quick-need" name="need" type="text" maxlength="300" autocomplete="off" placeholder="{{ __('site.hero.quick_ph') }}">
                    <button type="submit" data-track="hero_contact" class="btn btn-primary">{{ __('site.hero.cta_contact') }}</button>
                </div>
                <small>{{ __('site.hero.available') }}<br><a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" data-track="whatsapp">WhatsApp</a> · <a href="#projects" data-nav data-track="hero_projects">{{ __('site.hero.cta_projects') }}</a></small>
            </form>
        </div>

        <figure class="hero-photo">
            <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" fetchpriority="high" decoding="async">
            <figcaption>Francisco Cavaco, Seixal</figcaption>
        </figure>
    </div>
</section>
