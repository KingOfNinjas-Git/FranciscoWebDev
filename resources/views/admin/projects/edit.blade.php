@extends('admin.layout')

@section('title', $project->title)

@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.projects.index') }}" class="btn btn-ghost px-4 py-2 text-sm font-medium">&larr; Back to projects</a>
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
            <form action="{{ route('admin.projects.images.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf
                <label class="field">
                    <span>Select image (max 5 MB)</span>
                    <input type="file" name="image" accept="image/*" required>
                </label>
                <label class="field">
                    <span>Alt text</span>
                    <input type="text" name="alt" maxlength="255" placeholder="Describe the image for accessibility">
                </label>
                <label class="field">
                    <span>Caption</span>
                    <input type="text" name="caption" placeholder="Optional">
                </label>
                <button type="submit" class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">Upload</button>
            </form>
        </section>

        <section class="glass rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold text-white">Images ({{ $project->images->count() }})</h2>

            @forelse ($project->images as $img)
                @if ($loop->first)<div class="mt-5 grid grid-cols-2 gap-4">@endif
                <figure class="overflow-hidden rounded-2xl border border-white/10 bg-slate-950/50">
                    <div class="relative">
                        <img src="{{ asset($img->public_path) }}" alt="{{ $img->alt }}" class="h-32 w-full object-cover">
                        @if ($loop->first)
                            <span class="absolute left-2 top-2 rounded-full bg-sky-500/80 px-2 py-0.5 font-mono text-[10px] uppercase tracking-widest text-white">Cover</span>
                        @endif
                    </div>
                    <figcaption class="flex items-center justify-between gap-2 p-2">
                        @if (! $loop->first)
                            <form action="{{ route('admin.images.cover', $img->id) }}" method="POST">
                                @csrf
                                <button class="rounded-full px-2.5 py-1 text-xs font-medium text-sky-300 hover:bg-white/10">Make cover</button>
                            </form>
                        @else
                            <span></span>
                        @endif
                        <form action="{{ route('admin.images.destroy', $img->id) }}" method="POST" data-confirm="Delete this image?">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full px-2.5 py-1 text-xs font-medium text-rose-300 hover:bg-white/10">Delete</button>
                        </form>
                    </figcaption>
                </figure>
                @if ($loop->last)</div>@endif
            @empty
                <div class="mt-5 rounded-2xl border border-dashed border-white/15 p-8 text-center text-sm text-slate-400">No images uploaded yet.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
