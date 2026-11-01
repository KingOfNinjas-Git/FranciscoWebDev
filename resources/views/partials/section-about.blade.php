@php
    $skills = [
        'frontend' => ['HTML', 'CSS', 'JavaScript', 'React', 'Tailwind'],
        'backend' => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Blade'],
        'workflow' => ['Git', 'GitHub', 'Vite', 'Debugging', 'Deployment'],
        'focus' => __('site.about.focus'),
    ];
@endphp

<section id="about" class="section-anchor band-white px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-10">
            <p class="eyebrow">{{ __('site.about.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.about.title') }}</h2>
        </header>

        <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="reveal space-y-5 text-lg text-slate-300">
                <p>{{ __('site.about.p1') }}</p>
                <p>{{ __('site.about.p2') }}</p>
                <p>{{ __('site.about.p3') }}</p>
            </div>

            <div class="reveal">
                <h3 class="mb-5 text-xl font-extrabold text-white">{{ __('site.about.achievements') }}</h3>
                <ol class="timeline space-y-6">
                    @foreach (__('site.about.ach') as $item)
                        <li class="timeline-item">
                            <p class="font-bold text-white">{{ $item['title'] }}</p>
                            <p class="mt-1 text-slate-400">{{ $item['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="mt-16">
            <h3 class="reveal mb-6 text-xl font-extrabold text-white">{{ __('site.about.skills') }}</h3>
            <div class="grid gap-x-10 gap-y-7 sm:grid-cols-2">
                @foreach ($skills as $group => $items)
                    <div class="reveal">
                        <h4 class="mb-3 font-bold text-green-800" style="color:var(--green)">{{ __('site.about.groups.' . $group) }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($items as $item)
                                <span class="chip">{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="reveal mt-14">
            <h3 class="mb-5 text-xl font-extrabold text-white">{{ __('site.about.strengths') }}</h3>
            <ul class="grid gap-3 text-slate-300 md:grid-cols-2">
                @foreach (__('site.about.str') as $item)
                    <li class="flex items-start gap-3">
                        <span class="mt-2.5 h-2 w-2 shrink-0 rounded-full" style="background:var(--red)"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
