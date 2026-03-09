<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-950 min-h-screen font-sans overflow-x-hidden">
    <!-- Enhanced Particles Canvas -->
    <canvas id="particles-canvas" class="fixed inset-0 pointer-events-none z-10"></canvas>

    <!-- Floating Elements -->
    <div class="fixed inset-0 pointer-events-none z-5">
        <div class="absolute top-20 left-10 w-20 h-20 bg-cyan-400/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-32 h-32 bg-purple-400/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 left-1/4 w-24 h-24 bg-blue-400/10 rounded-full blur-xl animate-pulse" style="animation-delay: 4s;"></div>
    </div>
    
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-10 md:py-16 relative z-20">
        <div class="text-center mb-12 md:mb-16">
            <div class="inline-block mb-6">
                <h1 id="projects-typewriter" class="text-5xl md:text-7xl font-bold text-gray-100 mb-4 drop-shadow-lg animate-fade-in-up">
                    My Projects
                </h1>
                <div class="w-full h-1 bg-gradient-to-r from-indigo-600 via-cyan-400 to-blue-600 rounded-full animate-fade-in-up" style="animation-delay: 0.5s;"></div>
            </div>
            <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto animate-fade-in-up font-medium leading-relaxed" style="animation-delay: 1s;">
                A curated collection of my work spanning web development, design, and innovative solutions.
                Each project represents a unique challenge and learning experience.
            </p>

            <!-- Project Stats -->
            <div class="flex justify-center gap-8 mt-8 animate-fade-in-up" style="animation-delay: 1.5s;">
                <div class="text-center group">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-100">{{ $projects->count() }}</div>
                    <div class="text-sm text-gray-400 uppercase tracking-wide">Projects</div>
                </div>
                <div class="text-center group">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-code text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-100">{{ $projects->sum(fn($p) => count($p->tags ?? [])) }}</div>
                    <div class="text-sm text-gray-400 uppercase tracking-wide">Technologies</div>
                </div>
                <div class="text-center group">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-100">{{ now()->year - 2020 }}</div>
                    <div class="text-sm text-gray-400 uppercase tracking-wide">Years</div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($projects as $index => $project)
              <div class="bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/30 border border-slate-800 p-6 flex flex-col transition duration-300 hover:scale-105 hover:shadow-indigo-900/50 animate-fade-in-up project-card"
                  style="animation-delay: {{ $index * 120 }}ms;" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">

                <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" aria-label="Open {{ $project->title }} project" class="focus:outline-none">
                    <img loading="lazy" src="{{ $project->image_url }}" alt="{{ $project->title }}" class="rounded-lg mb-4 w-full h-48 object-cover shadow-lg">
                </a>

                <h3 class="text-2xl font-bold text-cyan-400 mb-2">{{ $project->title }}</h3>

                <p class="text-gray-300 leading-relaxed flex-grow mb-4">{{ $project->description }}</p>

                <div class="flex flex-wrap gap-2 mb-6">
                    @php
                        $tags = $project->tags;
                        $limit = 4;
                        $count = count($tags);
                    @endphp
                    @foreach (array_slice($tags, 0, $limit) as $tag)
                        <span class="bg-slate-800 text-cyan-400 px-3 py-1 rounded-full text-sm font-semibold border border-slate-700">{{ trim($tag) }}</span>
                    @endforeach
                    @if ($count > $limit)
                        <span class="bg-slate-800 text-cyan-400 px-3 py-1 rounded-full text-sm font-semibold border border-slate-700" title="{{ implode(', ', array_slice($tags, $limit)) }}">+{{ $count - $limit }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-4 mt-auto">
                        <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white w-full text-center rounded-lg px-4 py-2 font-semibold shadow-md shadow-indigo-900/40 hover:-translate-y-1 transition duration-300">Details</a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-10 rounded-2xl border border-slate-800 bg-slate-900/50">
                <p class="text-xl text-gray-400">No projects to display at the moment. Please check back later!</p>
            </div>
            @endforelse

        </div>
    </main>

    @include('partials.footer')
    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="fixed bottom-8 right-8 bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 translate-y-4 z-40 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <!-- Lightbox modal -->
    <div id="lightbox" class="hidden" aria-hidden="true">
        <div id="lb-backdrop" class="lightbox-backdrop" onclick="closeLightbox()" role="dialog" aria-modal="true">
            <div class="lightbox-content" onclick="event.stopPropagation()">
                <img id="lb-img" src="" alt="" style="max-width:100%;max-height:90vh;display:block">
                    <div class="p-4 text-center bg-slate-800"> <strong id="lb-title"></strong> </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/projects.js'])
    <script src="https://unpkg.com/vanilla-tilt@1.7.0/dist/vanilla-tilt.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>