<section id="projects" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12">
            <p class="eyebrow">{{ __('site.projects.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.projects.title') }}</h2>
            <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.projects.intro') }}</p>
        </header>

        @if ($projects->isEmpty())
            <p class="text-slate-400">{{ __('site.projects.empty') }}</p>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $i => $project)
                    <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug) }}" data-project-slug="{{ $project->slug }}" class="reveal project-tile glass glass-hover group block overflow-hidden rounded-3xl text-left" style="--d:{{ ($i % 3) * 90 }}ms">
                        <div class="project-media">
                            @if ($project->cover)
                                <img src="{{ $project->cover }}" @if ($project->cover_srcset) srcset="{{ $project->cover_srcset }}" sizes="(min-width: 1024px) 380px, (min-width: 768px) 45vw, 92vw" @endif alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center font-mono text-sm text-slate-400">{{ $project->title }}</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-semibold text-white">{{ $project->title }}</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach (array_slice($project->tags, 0, 4) as $tag)
                                    <span class="chip">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <p class="mt-4 font-mono text-xs uppercase tracking-[0.2rem] text-sky-300 transition group-hover:text-white">{{ __('site.projects.open') }} &rarr;</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
