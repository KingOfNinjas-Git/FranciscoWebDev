@php
    $skills = [
        'frontend' => ['HTML', 'CSS', 'JavaScript', 'React', 'Tailwind'],
        'backend' => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Blade'],
        'workflow' => ['Git', 'GitHub', 'Vite', 'Debugging', 'Deployment'],
        'focus' => __('site.about.focus'),
    ];
@endphp

<section id="about" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12">
            <p class="eyebrow">{{ __('site.about.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.about.title') }}</h2>
        </header>

        <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="reveal space-y-5 text-lg leading-8 text-slate-300">
                <p>{{ __('site.about.p1') }}</p>
                <p>{{ __('site.about.p2') }}</p>
                <p>{{ __('site.about.p3') }}</p>
            </div>

            <div class="reveal glass rounded-3xl p-6" style="--d:120ms">
                <h3 class="mb-5 text-lg font-semibold text-white">{{ __('site.about.achievements') }}</h3>
                <ol class="timeline space-y-6">
                    @foreach (__('site.about.ach') as $item)
                        <li class="timeline-item">
                            <p class="font-semibold text-white">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ $item['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="mt-16">
            <h3 class="reveal mb-6 text-lg font-semibold text-white">{{ __('site.about.skills') }}</h3>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($skills as $group => $items)
                    <div class="reveal glass glass-hover rounded-2xl p-5" style="--d:{{ $loop->index * 80 }}ms">
                        <h4 class="mb-4 text-lg font-semibold text-white">{{ __('site.about.groups.' . $group) }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($items as $item)
                                <span class="chip">{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="reveal glass mt-12 rounded-3xl p-6 md:p-8">
            <h3 class="mb-5 text-lg font-semibold text-white">{{ __('site.about.strengths') }}</h3>
            <ul class="grid gap-3 text-slate-300 md:grid-cols-2">
                @foreach (__('site.about.str') as $item)
                    <li class="flex items-start gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400 shadow-[0_0_10px_#38bdf8]"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
