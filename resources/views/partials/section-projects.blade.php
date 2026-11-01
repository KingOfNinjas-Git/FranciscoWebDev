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
            @php
                // The first two projects get a big row each; the rest sit together in a compact grid so every project is visible without a long scroll
                $featured = $projects->take(2);
                $others = $projects->slice(2);
                $cover = function ($project, $sizes) {
                    return $project->cover
                        ? '<img src="' . e($project->cover) . '"' . ($project->cover_srcset ? ' srcset="' . e($project->cover_srcset) . '" sizes="' . $sizes . '"' : '') . ' alt="" loading="lazy" decoding="async">'
                        : '<div class="flex h-full items-center justify-center text-slate-400">' . e($project->title) . '</div>';
                };
            @endphp
            <div class="work-list">
                @foreach ($featured as $project)
                    <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug) }}" data-project-slug="{{ $project->slug }}" data-track="project_open" class="reveal project-tile work-piece group">
                        <div class="work-shot">{!! $cover($project, '(min-width: 860px) 600px, 92vw') !!}</div>
                        <div>
                            <h3>{{ $project->title }}</h3>
                            <p class="work-tags">{{ implode(' · ', array_slice($project->tags, 0, 4)) }}</p>
                            <span class="work-open">{{ __('site.projects.open') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            @if ($others->isNotEmpty())
                <div class="work-grid">
                    @foreach ($others as $project)
                        <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug) }}" data-project-slug="{{ $project->slug }}" data-track="project_open" class="reveal project-tile work-tile group">
                            <div class="work-shot">{!! $cover($project, '(min-width: 860px) 380px, 92vw') !!}</div>
                            <h3>{{ $project->title }}</h3>
                            <p class="work-tags">{{ implode(' · ', array_slice($project->tags, 0, 3)) }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</section>
<div class="wave wave-mist" aria-hidden="true" style="background-color:var(--brand);margin-bottom:0"></div>
