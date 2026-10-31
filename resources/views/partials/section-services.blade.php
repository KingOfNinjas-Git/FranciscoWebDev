<section id="services" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12">
            <p class="eyebrow">{{ __('site.services.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.services.title') }}</h2>
            <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.services.intro') }}</p>
        </header>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('site.services.items') as $i => $item)
                <article class="reveal glass flex flex-col rounded-3xl p-6" style="--d:{{ $i * 90 }}ms">
                    <h3 class="text-xl font-semibold text-white">{{ $item['title'] }}</h3>
                    <p class="mt-3 flex-1 text-sm leading-6 text-slate-400">{{ $item['text'] }}</p>
                    <a href="#contact" data-service="{{ $item['type'] }}" data-track="service_ask" class="mt-5 text-sm font-semibold text-sky-300 transition hover:text-white">{{ __('site.services.ask') }}</a>
                </article>
            @endforeach
        </div>

        <div class="reveal glass mt-8 rounded-3xl p-6 md:p-8">
            <h3 class="mb-4 text-lg font-semibold text-white">{{ __('site.services.included_title') }}</h3>
            <ul class="grid gap-3 text-slate-300 sm:grid-cols-2">
                @foreach (__('site.services.included') as $point)
                    <li class="flex items-start gap-3">
                        <svg class="mt-1 h-4 w-4 shrink-0 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-16">
            <header class="reveal mb-8">
                <p class="eyebrow">{{ __('site.process.eyebrow') }}</p>
                <h3 class="mt-3 text-2xl font-bold text-white md:text-3xl">{{ __('site.process.title') }}</h3>
            </header>
            <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('site.process.steps') as $i => $step)
                    <li class="reveal glass rounded-3xl p-6" style="--d:{{ $i * 90 }}ms">
                        <span class="text-sm font-semibold text-sky-300" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $step['title'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
