<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Projects | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 min-h-screen font-sans">
    @include('navbar')

    <main class="container mx-auto p-6 py-12">
        <!-- Header -->
        <div class="mb-12">
            <div class="bg-gradient-to-r from-indigo-600 via-cyan-600 to-blue-600 rounded-3xl p-8 text-white shadow-2xl shadow-indigo-400/40 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold">Projects Admin</h1>
                    <p class="text-indigo-100 text-sm mt-2">Manage your portfolio projects and images</p>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="px-6 py-3 bg-slate-800 hover:bg-slate-700 backdrop-blur-md rounded-full text-gray-100 font-semibold transition shadow-lg border border-slate-600">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-indigo-900/30 border-l-4 border-indigo-500 text-indigo-300 rounded-lg shadow-md">
                <span class="font-semibold">✓</span> {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $p)
            <div class="group rounded-2xl overflow-hidden bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900/80 shadow-lg hover:shadow-2xl hover:shadow-indigo-600/20 border border-slate-700 transition duration-300 hover:-translate-y-1 backdrop-blur-sm">
                <!-- Image Container -->
                <div class="relative h-48 bg-gradient-to-br from-indigo-400 to-cyan-400 overflow-hidden">
                    @if($p->images()->count())
                        @php $first = $p->images()->first(); @endphp
                        <img src="{{ asset($first ? $first->public_path : '') }}" alt="{{ $p->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-500 text-center">
                            <span class="text-sm font-medium">No images yet</span>
                        </div>
                    @endif
                    <!-- Image Count Badge -->
                    <div class="absolute top-3 right-3 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                        {{ $p->images()->count() }} img
                    </div>
                </div>

                <!-- Content -->
                <div class="p-5">
                    <h3 class="text-xl font-extrabold text-gray-100 mb-2">{{ $p->title }}</h3>
                    <p class="text-sm text-gray-400 line-clamp-2 mb-4">{{ $p->description }}</p>

                    <!-- Tech Tags -->
                    @if($p->technologies && count($p->technologies) > 0)
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach(array_slice($p->technologies, 0, 2) as $tech)
                            <span class="inline-block bg-slate-700 text-cyan-400 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $tech }}</span>
                        @endforeach
                        @if(count($p->technologies) > 2)
                            <span class="inline-block text-gray-500 text-xs font-medium">+{{ count($p->technologies) - 2 }}</span>
                        @endif
                    </div>
                    @endif

                    <!-- Action Button -->
                    <a href="{{ route('admin.projects.edit', $p->id) }}" class="block text-center bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 hover:from-indigo-700 hover:via-blue-700 hover:to-blue-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-indigo-600/40 hover:shadow-indigo-600/60 transition duration-200">
                        Manage Project
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($projects) === 0)
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">No projects yet. Create one to get started!</p>
        </div>
        @endif
    </main>
</body>
</html>
