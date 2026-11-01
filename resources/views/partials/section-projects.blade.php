<div class="wave wave-green" aria-hidden="true" style="margin-top:4.5rem"></div>
<section id="projects" class="section-anchor work-band">
    <div class="work-inner">
        <header>
            <p class="eyebrow">{{ __('site.projects.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.projects.title') }}</h2>
            <p class="work-intro">{{ __('site.projects.intro') }}</p>
        </header>

        @if ($projects->isEmpty())
            <p class="work-empty">{{ __('site.projects.empty') }}</p>
        @else
            <div class="work-list">
                @foreach ($projects as $i => $project)
                    <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug) }}" data-project-slug="{{ $project->slug }}" data-track="project_open" class="reveal project-tile work-piece group">
                        <div class="work-shot">
                            @if ($project->cover)
                                <img src="{{ $project->cover }}" @if ($project->cover_srcset) srcset="{{ $project->cover_srcset }}" sizes="(min-width: 860px) 600px, 92vw" @endif alt="" loading="lazy" decoding="async">
                            @else
                                <div class="flex h-full items-center justify-center text-slate-400">{{ $project->title }}</div>
                            @endif
                        </div>
                        <div>
                            <h3>{{ $project->title }}</h3>
                            <p class="work-tags">{{ implode(' · ', array_slice($project->tags, 0, 4)) }}</p>
                            <span class="work-open">{{ __('site.projects.open') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
<div class="wave wave-mist" aria-hidden="true" style="background-color:var(--green);margin-bottom:0"></div>
