<section id="faq" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-3xl">
        <header class="reveal mb-10">
            <p class="eyebrow">{{ __('site.faq.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.faq.title') }}</h2>
        </header>

        <div class="space-y-3">
            @foreach (__('site.faq.items') as $i => $item)
                <details class="faq-item reveal glass rounded-2xl" style="--d:{{ $i * 60 }}ms">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold text-white">
                        <span>{{ $item['q'] }}</span>
                        <svg class="faq-chevron h-4 w-4 shrink-0 text-sky-300 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <p class="px-5 pb-5 leading-7 text-slate-300">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
