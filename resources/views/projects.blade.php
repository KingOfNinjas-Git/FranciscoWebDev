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
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-10 md:py-16">
        <div class="text-center mb-8 md:mb-12">
            <h1 class="text-4xl md:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-400 drop-shadow-lg">
                My Projects
            </h1>
            <p class="text-lg md:text-xl text-gray-700 mt-4">A selection of my work. Feel free to explore!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($projects as $index => $project)
              <div class="bg-gradient-to-b from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl shadow-lg shadow-indigo-200/50 border border-white/80 p-6 flex flex-col transition duration-300 hover:scale-105 hover:shadow-indigo-300/50 animate-fade-in-up project-card backdrop-blur-sm"
                  style="animation-delay: {{ $index * 120 }}ms;" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">

                <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" aria-label="Open {{ $project->title }} project" class="focus:outline-none tilt" data-tilt data-tilt-max="10">
                    <img loading="lazy" src="{{ $project->image_url }}" alt="{{ $project->title }}" class="rounded-lg mb-4 w-full h-48 object-cover shadow-lg">
                </a>

                <h3 class="text-2xl font-bold text-indigo-700 mb-2">{{ $project->title }}</h3>

                <p class="text-gray-800 leading-relaxed flex-grow mb-4">{{ $project->description }}</p>

                <div class="flex flex-wrap gap-2 mb-6">
                    @php
                        $tags = $project->tags;
                        $limit = 4;
                        $count = count($tags);
                    @endphp
                    @foreach (array_slice($tags, 0, $limit) as $tag)
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">{{ trim($tag) }}</span>
                    @endforeach
                    @if ($count > $limit)
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold" title="{{ implode(', ', array_slice($tags, $limit)) }}">+{{ $count - $limit }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-4 mt-auto">
                        <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" class="bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 hover:from-indigo-600 hover:via-cyan-600 hover:to-emerald-600 text-white w-full text-center rounded-lg px-4 py-2 font-semibold shadow-md shadow-indigo-400/40 hover:-translate-y-1 transition duration-300">Details</a>
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
    <!-- Lightbox modal -->
    <div id="lightbox" class="hidden" aria-hidden="true">
        <div id="lb-backdrop" class="lightbox-backdrop" onclick="closeLightbox()" role="dialog" aria-modal="true">
            <div class="lightbox-content" onclick="event.stopPropagation()">
                <img id="lb-img" src="" alt="" style="max-width:100%;max-height:90vh;display:block">
                <div class="p-4 text-center bg-white/80"> <strong id="lb-title"></strong> </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vanilla-tilt@1.7.0/dist/vanilla-tilt.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        document.addEventListener('DOMContentLoaded', function(){
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
            // VanillaTilt on .tilt elements
            if(window.VanillaTilt){ VanillaTilt.init(document.querySelectorAll('.tilt')) }
        });

        function openLightbox(src, title){
            const lb = document.getElementById('lightbox');
            const img = document.getElementById('lb-img');
            const t = document.getElementById('lb-title');
            img.src = src;
            img.alt = title || '';
            t.textContent = title || '';
            lb.classList.remove('hidden');
            lb.setAttribute('aria-hidden','false');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox(){
            const lb = document.getElementById('lightbox');
            lb.classList.add('hidden');
            lb.setAttribute('aria-hidden','true');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeLightbox(); } });
    </script>
    <script>
        // Small enhancement: reveal any project-card when scrolled into view (fallback for AOS)
        (function(){
            const cards = document.querySelectorAll('.project-card');
            if(!('IntersectionObserver' in window)) return;
            const io = new IntersectionObserver((entries)=>{
                entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('animate-fade-in-up'); });
            },{threshold:0.12});
            cards.forEach(c=> io.observe(c));
        })();
    </script>
</body>
</html>