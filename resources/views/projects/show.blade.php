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
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .swiper-pagination-bullet { background-color: #cbd5e1; opacity: 0.8; transition: all 0.3s; }
        .swiper-pagination-bullet-active { background-color: #6366f1; width: 24px; border-radius: 12px; }
        .font-comic { font-family: 'Comic Neue', cursive; }
    </style>
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
                <div class="swiper mySwiper bg-white/40 backdrop-blur-xl rounded-[2rem] shadow-2xl shadow-indigo-500/25 border border-white/60 p-3 transform transition hover:scale-[1.01] duration-500">
                    <div class="swiper-wrapper">
                        @php
                            $images = [];
                            if (isset($project->images) && is_iterable($project->images)) {
                                $images = $project->images;
                            }

                            // Prepare data for Lightbox JS
                            $jsImages = [];
                            if (count($images) > 0) {
                                foreach($images as $img) {
                                    $jsImages[] = [
                                        'src' => asset($img->public_path),
                                        'alt' => $project->title . ($img->caption ? ' — ' . $img->caption : ''),
                                    ];
                                }
                            } else {
                                $src = isset($project->image) ? asset($project->image) : "https://via.placeholder.com/1200x800?text=" . urlencode($project->title ?? 'Project');
                                $jsImages[] = [
                                    'src' => $src,
                                    'alt' => $project->title ?? 'Project'
                                ];
                            }
                        @endphp

                        @forelse($images as $img)
                            <div class="swiper-slide flex items-center justify-center rounded-2xl overflow-hidden">
                                <img loading="lazy" src="{{ asset($img->public_path) }}" alt="{{ $img->alt ?? $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl cursor-zoom-in transition-transform duration-300 hover:scale-[1.02]" onclick="openLightbox({{ $loop->index }})">
                            </div>
                        @empty
                            <div class="swiper-slide flex items-center justify-center rounded-2xl overflow-hidden">
                                @if(isset($project->image))
                                    <img loading="lazy" src="{{ asset($project->image) }}" alt="{{ $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl cursor-zoom-in transition-transform duration-300 hover:scale-[1.02]" onclick="openLightbox(0)">
                                @else
                                    <img loading="lazy" src="https://via.placeholder.com/1200x800?text={{ urlencode($project->title ?? 'Project') }}" alt="{{ $project->title }}" class="w-full h-auto max-h-[60vh] object-contain rounded-2xl cursor-zoom-in transition-transform duration-300 hover:scale-[1.02]" onclick="openLightbox(0)">
                                @endif
                            </div>
                        @endforelse
                    </div>
                    <!-- If we need navigation buttons -->
                    <button class="custom-prev hidden md:flex absolute top-1/2 left-4 z-20 -translate-y-1/2 w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-lg shadow-indigo-500/30 items-center justify-center transition-all duration-300 hover:-translate-x-1 group disabled:opacity-0 disabled:cursor-default">
                        <svg class="w-6 h-6 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <button class="custom-next hidden md:flex absolute top-1/2 right-4 z-20 -translate-y-1/2 w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-lg shadow-indigo-500/30 items-center justify-center transition-all duration-300 hover:translate-x-1 group disabled:opacity-0 disabled:cursor-default">
                        <svg class="w-6 h-6 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <div class="swiper-pagination !bottom-4"></div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/60 p-6 md:p-10 animate-fade-in-up">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 mb-6 drop-shadow-sm leading-tight">{{ $project->title }}</h1>
                    
                    <!-- Project Metadata -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-indigo-800/80 font-comic font-bold mb-8 bg-indigo-50/80 inline-flex px-4 py-2 rounded-xl border border-indigo-100">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            @if(isset($project->created_at))
                                <time datetime="{{ $project->created_at->format('Y-m-d') }}">{{ $project->created_at->format('F Y') }}</time>
                            @else
                                Recently Added
                            @endif
                        </span>
                        <span class="text-indigo-300">|</span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            {{ $project->category ?? 'Web Project' }}
                        </span>
                    </div>

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
                    <div class="text-gray-800 text-lg leading-relaxed whitespace-pre-wrap font-comic">{{ $project->notes ?? '' }}</div>

                    <!-- CTA Section -->
                    <div class="mt-12 p-8 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border-2 border-indigo-100 border-dashed text-center relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-400 to-purple-400 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
                        <h3 class="font-comic font-bold text-2xl text-indigo-900 mb-3">Interested in this project?</h3>
                        <p class="text-indigo-700/80 mb-6 font-comic text-lg">I'm always open to discussing the details or starting something new.</p>
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold py-3 px-8 rounded-full hover:bg-indigo-700 transition-all shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-1">
                            <span>Get in Touch</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Live/Source links removed per preference; use notes and technologies instead -->
            </div>
        </div>
    </main>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[9999] bg-slate-900/95 backdrop-blur-xl hidden opacity-0 transition-all duration-300 flex items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-6 right-6 text-white/60 hover:text-white bg-white/5 hover:bg-white/10 rounded-full p-3 transition-all duration-300 border border-white/10 shadow-lg group z-50" aria-label="Close">
            <svg class="w-6 h-6 transform group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <!-- Lightbox Navigation -->
        <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-4 transition-colors z-50 hover:scale-110 duration-300" onclick="changeLightboxImage(-1); event.stopPropagation()" aria-label="Previous Image">
            <svg class="w-10 h-10 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-4 transition-colors z-50 hover:scale-110 duration-300" onclick="changeLightboxImage(1); event.stopPropagation()" aria-label="Next Image">
            <svg class="w-10 h-10 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <div class="relative max-w-7xl w-full max-h-full flex flex-col items-center justify-center" onclick="event.stopPropagation()">
            <img id="lightbox-img" src="" alt="Full screen view" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl shadow-black/50 transform scale-95 opacity-0 transition-all duration-500 ease-out">
            <p id="lightbox-caption" class="mt-4 text-white/80 text-center text-lg font-medium tracking-wide opacity-0 transform translate-y-4 transition-all duration-500 delay-100"></p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        const lightboxImages = @json($jsImages);
        let currentLightboxIndex = 0;

        function openLightbox(index) {
            currentLightboxIndex = index;
            updateLightboxContent();

            const lightbox = document.getElementById('lightbox');
            const img = document.getElementById('lightbox-img');
            const caption = document.getElementById('lightbox-caption');
            
            lightbox.classList.remove('hidden');
            // Force reflow to enable transition
            void lightbox.offsetWidth;
            
            lightbox.classList.remove('opacity-0');
            
            img.classList.remove('scale-95', 'opacity-0');
            img.classList.add('scale-100', 'opacity-100');
            
            if(caption) {
                caption.classList.remove('opacity-0', 'translate-y-4');
                caption.classList.add('opacity-100', 'translate-y-0');
            }

            document.body.classList.add('overflow-hidden');
        }

        function updateLightboxContent() {
            const img = document.getElementById('lightbox-img');
            const caption = document.getElementById('lightbox-caption');
            const data = lightboxImages[currentLightboxIndex];
            
            if (!data) return;

            img.src = data.src;
            img.alt = data.alt;
            if(caption) caption.textContent = data.alt;
        }

        function changeLightboxImage(direction) {
            currentLightboxIndex = (currentLightboxIndex + direction + lightboxImages.length) % lightboxImages.length;
            const img = document.getElementById('lightbox-img');
            img.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                updateLightboxContent();
                img.classList.remove('opacity-0', 'scale-95');
            }, 200);
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            const img = document.getElementById('lightbox-img');
            const caption = document.getElementById('lightbox-caption');

            lightbox.classList.add('opacity-0');
            
            img.classList.remove('scale-100', 'opacity-100');
            img.classList.add('scale-95', 'opacity-0');
            
            if(caption) {
                caption.classList.remove('opacity-100', 'translate-y-0');
                caption.classList.add('opacity-0', 'translate-y-4');
            }

            document.body.classList.remove('overflow-hidden');
            
            setTimeout(() => {
                lightbox.classList.add('hidden');
                img.src = '';
            }, 300);
        }

        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft') changeLightboxImage(-1);
            if (e.key === 'ArrowRight') changeLightboxImage(1);
            if (e.key === 'Escape') closeLightbox();
        });

        document.addEventListener('DOMContentLoaded', function(){
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
            const swiper = new Swiper('.mySwiper', {
                loop: true,
                lazy: true,
                effect: 'slide',
                grabCursor: true,
                spaceBetween: 30,
                slidesPerView: 1,
                autoHeight: true,
                keyboard: { enabled: true },
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                navigation: { nextEl: '.custom-next', prevEl: '.custom-prev' },
                pagination: { el: '.swiper-pagination', clickable: true },
            });
        });
    </script>
</body>
</html>
