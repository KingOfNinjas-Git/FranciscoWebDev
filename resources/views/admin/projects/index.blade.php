<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin ? Projects | Francisco Cavaco</title>
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
                    <h1 class="mt-3 text-3xl font-semibold text-white md:text-4xl">Projects</h1>
                </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.projects.create') }}" class="site-button mr-2 px-4 py-2 text-sm font-medium text-slate-200">Add project</a>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="site-button px-4 py-2 text-sm font-medium text-slate-200">Logout</button>
                </form>
            </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($projects as $p)
                <article class="site-card overflow-hidden rounded-3xl border border-slate-800">
                    <div class="relative h-48 bg-slate-900">
                        @if($p->images()->count())
                            @php $first = $p->images()->first(); @endphp
                            <img src="{{ asset($first ? $first->public_path : '') }}" alt="{{ $p->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-slate-400">
                                No images yet
                            </div>
                        @endif
                        <span class="absolute right-3 top-3 rounded-full border border-slate-700 bg-slate-950/80 px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.18rem] text-slate-200">
                            {{ $p->images()->count() }} img
                        </span>
                    </div>

                    <div class="p-5">
                        <h2 class="text-xl font-semibold text-white">{{ $p->title }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-300">{{ $p->description }}</p>

                        @if($p->technologies && count($p->technologies) > 0)
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach(array_slice($p->technologies, 0, 3) as $tech)
                                    <span class="rounded-full border border-slate-700 bg-slate-900 px-2.5 py-1 text-[11px] font-medium text-slate-200">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('admin.projects.edit', $p->id) }}" class="site-button site-button-primary mt-5 w-full px-4 py-3 text-sm font-medium text-white">
                            Manage project
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if(count($projects) === 0)
            <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/40 p-10 text-center text-slate-400">
                No projects yet.
            </div>
        @endif
    </main>
</body>
</html>
