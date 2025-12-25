<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->title ?? 'Project' }} | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-12">
        <div class="mb-8">
            <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/80 hover:bg-white text-indigo-700 rounded-full font-bold shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-x-1 group backdrop-blur-sm">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Projects
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            <div class="lg:col-span-5 lg:sticky lg:top-24 z-10">
                <!-- Swiper -->
                <div class="swiper mySwiper bg-white/30 backdrop-blur-md rounded-3xl shadow-2xl shadow-indigo-500/20 border border-white/50 p-2 transform transition hover:scale-[1.01] duration-500">
                    <div class="swiper-wrapper">
                        @php
                            $images = [];
                            if (isset($project->images) && is_iterable($project->images)) {
                                $images = $project->images;
                            }
                        @endphp

                        @forelse($images as $img)
                            <div class="swiper-slide flex items-center justify-center rounded-2xl overflow-hidden">
                                <img loading="lazy" src="{{ asset($img->public_path) }}" alt="{{ $img->alt ?? $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl">
                            </div>
                        @empty
                            <div class="swiper-slide flex items-center justify-center rounded-2xl overflow-hidden">
                                @if(isset($project->image))
                                    <img loading="lazy" src="{{ asset($project->image) }}" alt="{{ $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl">
                                @else
                                    <img loading="lazy" src="https://via.placeholder.com/1200x800?text={{ urlencode($project->title ?? 'Project') }}" alt="{{ $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl">
                                @endif
                            </div>
                        @endforelse
                    </div>
                    <!-- If we need navigation buttons -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/60 p-6 md:p-10 animate-fade-in-up">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 mb-6 drop-shadow-sm leading-tight">{{ $project->title }}</h1>
                    
                    <!-- Intro / Short Description -->
                    <div class="text-xl text-indigo-900 font-medium mb-8 leading-relaxed border-l-4 border-indigo-400 pl-6 italic bg-indigo-50/50 py-2 rounded-r-lg">
                        {{ $project->description ?? '' }}
                    </div>

                    <div class="mb-8">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Technologies</h4>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $techs = $project->technologies ?? ($project->tags ?? []);
                            @endphp
                            @foreach(is_array($techs) ? $techs : explode(',', $techs) as $t)
                                <span class="bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-700 border border-indigo-200 px-4 py-1.5 rounded-full text-sm font-bold shadow-sm">{{ trim($t) }}</span>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-indigo-100 my-8">
                    
                    <!-- Main Content (Notes) -->
                    <div class="text-gray-800 text-lg leading-relaxed whitespace-pre-wrap font-light">{{ $project->notes ?? '' }}</div>
                </div>

                <!-- Live/Source links removed per preference; use notes and technologies instead -->
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
            const swiper = new Swiper('.mySwiper', {
                loop: true,
                lazy: true,
                keyboard: { enabled: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                pagination: { el: '.swiper-pagination', clickable: true },
            });
        });
    </script>
</body>
</html>
