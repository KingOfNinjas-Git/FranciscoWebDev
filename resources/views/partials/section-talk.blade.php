@php
    // Simple line icons for the three ways to talk (same order as site.talk.ways)
    $icons = [
        'M15 19.1c0-2.2-2.7-4-6-4s-6 1.8-6 4M9 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm9 2.5c1.6.4 3 1.4 3 2.9M16 5.2a3.5 3.5 0 0 1 0 6.6',
        'M3 8.5A2.5 2.5 0 0 1 5.5 6h8A2.5 2.5 0 0 1 16 8.5v7a2.5 2.5 0 0 1-2.5 2.5h-8A2.5 2.5 0 0 1 3 15.5v-7Zm13 2.5 5-3v9l-5-3',
        'M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4h0A2.5 2.5 0 0 1 4 13.5v-8Z',
    ];
@endphp

<section id="talk" class="section-anchor px-4 py-20">
    <div class="talk-grid">
        <div>
            <h2 class="section-title">{{ __('site.talk.title') }}</h2>
            <p class="mt-5 max-w-xl text-lg text-slate-400">{{ __('site.talk.text') }}</p>
            <p class="mt-4 max-w-xl text-lg text-slate-400">{{ __('site.talk.why') }}</p>
            <a href="#contact" data-nav class="btn btn-primary mt-8 px-7 py-3.5 text-base">{{ __('site.talk.cta') }}</a>
        </div>

        <div>
            <h3 class="sr-only">{{ __('site.talk.ways_title') }}</h3>
            <ul class="talk-ways">
                @foreach (__('site.talk.ways') as $i => $way)
                    <li class="talk-way">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icons[$i] ?? $icons[0] }}"/></svg>
                        <span>
                            <strong>{{ $way['title'] }}</strong>
                            <span class="text-slate-400">{{ $way['text'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
