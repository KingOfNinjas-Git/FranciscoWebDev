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
                    <button type="button" data-project="{{ $i }}" class="reveal project-tile glass glass-hover group overflow-hidden rounded-3xl text-left" style="--d:{{ ($i % 3) * 90 }}ms">
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
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <dialog id="project-modal" class="project-modal glass" aria-labelledby="pm-title"
            data-l-close="{{ __('site.projects.close') }}" data-l-stack="{{ __('site.projects.stack') }}"
            data-l-details="{{ __('site.projects.details') }}" data-l-live="{{ __('site.projects.live') }}"
            data-l-repo="{{ __('site.projects.repo') }}" data-l-prev="{{ __('site.projects.prev') }}"
            data-l-next="{{ __('site.projects.next') }}">
        <div class="flex items-start justify-between gap-4 p-5 md:p-6">
            <div>
                <h3 id="pm-title" class="text-2xl font-bold text-white"></h3>
                <p id="pm-year" class="mt-1 font-mono text-xs uppercase tracking-[0.2rem] text-slate-400"></p>
            </div>
            <button type="button" id="pm-close" class="glass-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-200" aria-label="{{ __('site.projects.close') }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </div>
        <div id="pm-body" class="space-y-5 px-5 pb-6 md:px-6"></div>
    </dialog>
</section>
