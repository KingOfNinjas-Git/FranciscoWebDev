@extends('admin.layout')

@section('title', $project->title)

@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.projects.index') }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">&larr; Back to projects</a>
    <a href="{{ \App\Http\Middleware\SetLocale::projectUrl($project->slug, 'en') }}" target="_blank" rel="noopener" class="btn btn-ghost px-4 py-2 text-sm font-medium">View public page &nearr;</a>
    <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" data-confirm="Delete &quot;{{ $project->title }}&quot; and all its images? This cannot be undone.">
        @csrf
        @method('DELETE')
        <button class="btn btn-ghost px-4 py-2 text-sm font-medium text-rose-300 hover:!border-rose-400/60">Delete project</button>
    </form>
</div>

<div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
    <section class="glass rounded-3xl p-6 md:p-8">
        <h1 class="text-3xl font-bold text-white">{{ $project->title }}</h1>
        <p class="mb-6 mt-2 text-sm text-slate-400">Edit the project's text in each language and its details.</p>
        @include('admin.projects._form')
    </section>

    <div class="space-y-8">
        <section class="glass rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold text-white">Upload image</h2>
            <p class="mt-1 text-xs text-slate-400">Images are resized to 1600px wide and saved as WebP automatically.</p>
            <form action="{{ route('admin.projects.images.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf
                <label class="field">
                    <span>Select image (max 5 MB)</span>
                    <input type="file" name="image" accept="image/*" required>
                </label>
                @include('admin.projects._image-text', ['img' => null])
                <button type="submit" class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">Upload</button>
            </form>
        </section>

        <section class="glass rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold text-white">Demo video</h2>
            <p class="mt-1 text-xs text-slate-400">Optional. A short screen recording (max 30 MB each). It shows on the public project page under the cover image, with the cover as its poster, and never plays by itself. Add the <strong>same video in both formats</strong>: MP4 plays on every phone and computer, WebM is smaller in browsers that have it. Browsers pick the first one they can play. Add a written version below (in the project's text boxes) for people who cannot watch it.</p>
            @foreach (['main' => ['demo_video', 'Main video'], 'second' => ['demo_video_fallback', 'Second format']] as $slot => [$column, $label])
                <div class="mt-5 border-t border-white/10 pt-5">
                    <h3 class="text-sm font-semibold text-slate-200">{{ $label }}</h3>
                    @if ($project->{$column})
                        <video src="{{ asset($project->{$column}) }}" controls muted playsinline preload="metadata" class="mt-3 w-full rounded-2xl border border-white/10"></video>
                        <form action="{{ route('admin.projects.video.destroy', $project->id) }}" method="POST" data-confirm="Remove this video?" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="slot" value="{{ $slot }}">
                            <button class="rounded-full px-2.5 py-1 text-xs font-medium text-rose-300 hover:bg-white/10">Remove video</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.projects.video.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="slot" value="{{ $slot }}">
                        <label class="field">
                            <span>{{ $project->{$column} ? 'Replace' : 'Select' }} (MP4 or WebM)</span>
                            <input type="file" name="video" accept="video/mp4,video/webm" required>
                        </label>
                        <button type="submit" class="btn btn-primary w-full px-4 py-2.5 text-sm font-semibold">Upload</button>
                    </form>
                </div>
            @endforeach
        </section>

        <section class="glass rounded-3xl p-6 md:p-8">
            <div class="flex items-baseline justify-between gap-3">
                <h2 class="text-xl font-semibold text-white">Images ({{ $project->images->count() }})</h2>
                @if ($project->images->count() > 1)
                    <p class="text-xs text-slate-400">Drag to reorder, or use the arrows. The first is the cover.</p>
                @endif
            </div>

            @if ($project->images->isEmpty())
                <div class="mt-5 rounded-2xl border border-dashed border-white/15 p-8 text-center text-sm text-slate-400">No images uploaded yet.</div>
            @else
                <div id="image-list" class="mt-5 grid grid-cols-2 gap-4" data-reorder-url="{{ route('admin.images.reorder', $project->id) }}">
                    @foreach ($project->images as $img)
                        <figure class="image-card overflow-hidden rounded-2xl border border-white/10 bg-slate-950/50" data-id="{{ $img->id }}">
                            <div class="relative">
                                <img src="{{ asset($img->public_path) }}" alt="{{ $img->alt }}" class="h-32 w-full select-none object-cover" draggable="false">
                                <span class="cover-badge absolute left-2 top-2 rounded-full bg-sky-500/80 px-2 py-0.5 font-mono text-[10px] uppercase tracking-widest text-white {{ $loop->first ? '' : 'hidden' }}">Cover</span>
                                @if ($project->images->count() > 1)
                                    <div class="absolute bottom-2 right-2 flex gap-1">
                                        <button type="button" data-move="-1" aria-label="Move earlier" class="move-btn">&larr;</button>
                                        <button type="button" data-move="1" aria-label="Move later" class="move-btn">&rarr;</button>
                                    </div>
                                @endif
                            </div>
                            <figcaption class="p-2">
                                <details class="group">
                                    <summary class="cursor-pointer list-none rounded-full px-2.5 py-1 text-xs font-medium text-sky-300 hover:bg-white/10">Edit text &darr;</summary>
                                    <form action="{{ route('admin.images.update', $img->id) }}" method="POST" class="mt-3 space-y-4">
                                        @csrf
                                        @method('PUT')
                                        @include('admin.projects._image-text', ['img' => $img])
                                        <button class="btn btn-primary w-full px-3 py-2 text-xs font-semibold">Save text</button>
                                    </form>
                                </details>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <form action="{{ route('admin.images.cover', $img->id) }}" method="POST" class="make-cover {{ $loop->first ? 'hidden' : '' }}">
                                        @csrf
                                        <button class="rounded-full px-2.5 py-1 text-xs font-medium text-sky-300 hover:bg-white/10">Make cover</button>
                                    </form>
                                    <form action="{{ route('admin.images.destroy', $img->id) }}" method="POST" data-confirm="Delete this image?" class="ml-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full px-2.5 py-1 text-xs font-medium text-rose-300 hover:bg-white/10">Delete</button>
                                    </form>
                                </div>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
