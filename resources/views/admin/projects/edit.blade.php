<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images ? {{ $project->title }}</title>
    @vite(['resources/css/app.css'])
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-6xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <div class="mb-8">
            <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-slate-500 hover:bg-slate-800">
                <span>?</span>
                Back to projects
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="site-card rounded-3xl border border-slate-800 p-6 md:p-8">
                <h1 class="text-3xl font-semibold text-white md:text-4xl">{{ $project->title }}</h1>
                <p class="mt-2 text-sm text-slate-400">Edit project details and content.</p>

                <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Project title</label>
                        <input type="text" name="title" value="{{ old('title', $project->title) }}" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Project date</label>
                        <input type="date" name="created_at" value="{{ old('created_at', isset($project->created_at) ? $project->created_at->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Short description</label>
                        <textarea name="description" rows="3" class="w-full resize-none rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" placeholder="Brief summary for the project list...">{{ old('description', $project->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Technologies (comma-separated)</label>
                        <input type="text" name="technologies" value="{{ old('technologies', is_array($project->technologies) ? implode(', ', $project->technologies) : $project->technologies) }}" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" placeholder="Laravel, Tailwind, JavaScript">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Project notes</label>
                        <textarea name="notes" rows="10" class="w-full resize-y rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" placeholder="Full project details...">{{ old('notes', $project->notes) }}</textarea>
                    </div>

                    <button type="submit" class="site-button site-button-primary w-full px-4 py-3 text-sm font-medium text-white">
                        Save details
                    </button>
                </form>
            </section>

            <div class="space-y-8">
                <section class="site-card rounded-3xl border border-slate-800 p-6 md:p-8">
                    <h2 class="text-2xl font-semibold text-white">Upload image</h2>
                    <form action="{{ route('admin.projects.images.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Select image</label>
                            <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-sm file:text-slate-100" required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Alt text</label>
                            <input type="text" name="alt" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" placeholder="Describe the image for accessibility">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Caption</label>
                            <input type="text" name="caption" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 focus:border-sky-500 focus:outline-none" placeholder="Optional image caption">
                        </div>

                        <button type="submit" class="site-button site-button-primary w-full px-4 py-3 text-sm font-medium text-white">
                            Upload image
                        </button>
                    </form>
                </section>

                <section class="site-card rounded-3xl border border-slate-800 p-6 md:p-8">
                    <h2 class="text-2xl font-semibold text-white">Images ({{ count($project->images) }})</h2>

                    @if(count($project->images) > 0)
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            @foreach($project->images as $img)
                                <div class="group relative overflow-hidden rounded-2xl border border-slate-700 bg-slate-900">
                                    <img src="{{ asset($img->public_path) }}" alt="{{ $img->alt }}" class="h-36 w-full object-cover">

                                    <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-slate-950/90 to-transparent p-3 opacity-0 transition group-hover:opacity-100">
                                        <p class="text-xs font-medium text-white">{{ $img->caption ?? $img->alt ?? 'Untitled' }}</p>
                                    </div>

                                    <form action="{{ route('admin.images.destroy', $img->id) }}" method="POST" class="absolute right-2 top-2 opacity-0 transition group-hover:opacity-100" onsubmit="return confirm('Delete this image?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-600 p-2 text-white hover:bg-red-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-6 rounded-2xl border border-dashed border-slate-700 bg-slate-900/40 p-8 text-center text-sm text-slate-400">
                            No images uploaded yet.
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>
</body>
</html>
