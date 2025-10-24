<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.8s cubic-bezier(.4,0,.2,1) both;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="container mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-400 drop-shadow-lg">
                My Projects
            </h1>
            <p class="text-xl text-gray-700 mt-4">A selection of my work. Feel free to explore!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">

            @forelse ($projects as $index => $project)
            <div class="glass rounded-2xl p-6 shadow-2xl border border-indigo-100 flex flex-col transition duration-300 hover:scale-105 hover:shadow-indigo-400/50 animate-fade-in-up"
                 style="animation-delay: {{ $index * 150 }}ms;">
                
                
                <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" class="rounded-lg mb-4 w-full h-auto object-cover shadow-lg">
                
                <h3 class="text-2xl font-bold text-indigo-700 mb-2">{{ $project->title }}</h3>
                
                <p class="text-gray-800 leading-relaxed flex-grow mb-4">{{ $project->description }}</p>

                <div class="flex flex-wrap gap-2 mb-6">
                   
                    @foreach (is_array($project->tags) ? $project->tags : explode(',', $project->tags) as $tag)
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">{{ trim($tag) }}</span>
                    @endforeach
                </div>

                <div class="flex items-center gap-4 mt-auto">
                    <a href="{{ $project->live_url }}" target="_blank" class="w-full text-center px-6 py-2 bg-indigo-600 text-white rounded-lg shadow-md font-semibold hover:bg-indigo-700 transition">
                        Live Demo
                    </a>
                    <a href="{{ $project->repo_url }}" target="_blank" class="w-full text-center px-6 py-2 bg-gray-700 text-white rounded-lg shadow-md font-semibold hover:bg-gray-800 transition">
                        Source Code
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-10 glass rounded-2xl">
                <p class="text-xl text-indigo-700">No projects to display at the moment. Please check back later!</p>
            </div>
            @endforelse

        </div>
    </main>

    <footer class="text-center py-6 bg-gradient-to-r from-indigo-700 via-purple-600 to-pink-500 text-white mt-16 rounded-t-xl shadow-lg">
        <p>&copy; {{ date('Y') }} Francisco Cavaco. All rights reserved.</p>
    </footer>
</body>
</html>