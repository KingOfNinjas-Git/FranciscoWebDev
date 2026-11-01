<section id="services" class="section-anchor px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-10">
            <p class="eyebrow">{{ __('site.services.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.services.title') }}</h2>
            <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.services.intro') }}</p>
        </header>

        <div class="service-list">
            @foreach (__('site.services.items') as $i => $item)
                <article class="reveal service-row">
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['text'] }}</p>
                    <a href="#contact" data-service="{{ $item['type'] }}" data-track="service_ask">{{ __('site.services.ask') }}</a>
                </article>
            @endforeach
        </div>

        <div class="reveal mt-10">
            <h3 class="mb-4 text-xl font-extrabold text-white">{{ __('site.services.included_title') }}</h3>
            <ul class="flex flex-wrap gap-2.5">
                @foreach (__('site.services.included') as $point)
                    <li class="chip !text-base !py-1.5 !px-4">{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="mt-20">
            <header class="reveal mb-10">
                <p class="eyebrow">{{ __('site.process.eyebrow') }}</p>
                <h3 class="section-title">{{ __('site.process.title') }}</h3>
            </header>
            <ol class="route">
                @foreach (__('site.process.steps') as $i => $step)
                    <li class="reveal">
                        <strong>{{ $step['title'] }}</strong>
                        <span>{{ $step['text'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
