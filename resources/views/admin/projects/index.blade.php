@extends('admin.layout')

@section('title', 'Projects')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="section-title">Projects</h1>
        <p class="mt-2 text-sm text-slate-400">{{ $projects->count() }} {{ \Illuminate\Support\Str::plural('project', $projects->count()) }}. The first image of each project is its cover.</p>
    </div>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary px-6 py-3 text-sm font-semibold">+ Add project</a>
</div>

@if ($projects->isEmpty())
    <div class="glass rounded-3xl border-dashed p-10 text-center text-slate-400">
        No projects yet. Until you add one, the site shows three built-in sample projects.
    </div>
@else
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($projects as $p)
            @php $cover = $p->images->first(); @endphp
            <article class="glass glass-hover flex flex-col overflow-hidden rounded-3xl">
                <div class="project-media relative">
                    @if ($cover)
                        <img src="{{ asset($cover->public_path) }}" alt="{{ $p->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-slate-500">No images yet</div>
                    @endif
                    <span class="absolute right-3 top-3 rounded-full border border-white/15 bg-slate-950/70 px-2.5 py-1 font-mono text-[10px] uppercase tracking-widest text-slate-200">{{ $p->images->count() }} img</span>
                </div>

                <div class="flex flex-1 flex-col p-5">
                    <h2 class="text-xl font-semibold text-white">{{ $p->title }}</h2>
                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-300">{{ $p->description }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach (array_slice($p->technologies ?? [], 0, 4) as $tech)
                            <span class="chip">{{ $tech }}</span>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center gap-2 font-mono text-[11px] uppercase tracking-widest">
                        <span class="text-slate-500">Translated:</span>
                        @foreach (App\Models\Project::TRANSLATION_LOCALES as $loc)
                            <span class="{{ $p->hasTranslation($loc) ? 'text-emerald-300' : 'text-slate-600' }}">{{ strtoupper($loc) }} {{ $p->hasTranslation($loc) ? '&#10003;' : '&ndash;' }}</span>
                        @endforeach
                    </div>

                    <div class="mt-auto flex gap-2 pt-5">
                        <a href="{{ route('admin.projects.edit', $p->id) }}" class="btn btn-primary flex-1 px-4 py-2.5 text-sm font-semibold">Manage</a>
                        <form action="{{ route('admin.projects.destroy', $p->id) }}" method="POST" data-confirm="Delete &quot;{{ $p->title }}&quot; and all its images? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost px-4 py-2.5 text-sm font-semibold text-rose-300 hover:!border-rose-400/60" aria-label="Delete {{ $p->title }}">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

