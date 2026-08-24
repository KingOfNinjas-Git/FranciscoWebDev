<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create project | Francisco Cavaco</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @include('navbar')

    <main class="mx-auto max-w-6xl px-4 pb-20 pt-12 md:px-6 md:pt-16">
        <div class="mb-10 rounded-3xl border border-slate-800 bg-slate-900/80 p-6 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Admin</p>
                    <h1 class="mt-3 text-3xl font-semibold text-white md:text-4xl">Create project</h1>
                </div>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="site-button px-4 py-2 text-sm font-medium text-slate-200">Logout</button>
                </form>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="site-card rounded-3xl border border-slate-800 p-6 md:p-8">
            <form action="{{ route('admin.projects.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Short description</label>
                    <textarea name="description" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200" rows="3">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Technologies (comma-separated)</label>
                    <input type="text" name="technologies" value="{{ old('technologies') }}" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200">
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Live URL</label>
                        <input type="url" name="live_url" value="{{ old('live_url') }}" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Repo URL</label>
                        <input type="url" name="repo_url" value="{{ old('repo_url') }}" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Notes</label>
                    <textarea name="notes" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200" rows="5">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Published date (optional)</label>
                    <input type="date" name="created_at" value="{{ old('created_at') }}" class="w-full rounded-lg border border-slate-800 bg-slate-900 px-4 py-2 text-slate-200">
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('admin.projects.index') }}" class="site-button mr-3 px-4 py-2 text-sm font-medium text-slate-200">Cancel</a>
                    <button type="submit" class="site-button site-button-primary px-6 py-3 text-sm font-medium text-white">Create project</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>