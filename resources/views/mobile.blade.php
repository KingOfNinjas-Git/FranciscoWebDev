<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Francisco Cavaco | Web Developer</title>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        .section { scroll-margin-top: 80px; }
    </style>
</head>
<body class="bg-slate-950 min-h-screen font-sans">
    <!-- Particles Canvas -->
    <canvas id="particles-canvas" class="fixed inset-0 pointer-events-none z-10"></canvas>

    <!-- Mobile Single Page Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800 md:hidden">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#home" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 flex items-center justify-center text-white text-xs font-bold">FC</div>
                <span class="text-lg font-bold text-gray-100">Francisco</span>
            </a>
            <button id="mobile-menu-toggle" class="text-gray-300 hover:text-cyan-400 p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden border-t border-slate-800 bg-slate-900/95 backdrop-blur px-4 py-3 space-y-2">
            <a href="#home" class="block py-2 text-gray-300 hover:text-cyan-400 font-semibold">Home</a>
            <a href="#about" class="block py-2 text-gray-300 hover:text-cyan-400 font-semibold">About</a>
            <a href="#projects" class="block py-2 text-gray-300 hover:text-cyan-400 font-semibold">Projects</a>
            <a href="#contact" class="block py-2 text-gray-300 hover:text-cyan-400 font-semibold">Contact</a>
        </div>
    </nav>

    <!-- Spacer for fixed mobile navbar -->
    <div class="h-16 md:hidden"></div>

    <!-- Desktop Navbar (includes 'navbar' component) -->
    <div class="hidden md:block">
        @include('navbar')
    </div>

    <main>
        <!-- Hero/Welcome Section -->
        <section id="home" class="section pt-24 pb-12 px-4">
            <div class="max-w-2xl md:max-w-4xl mx-auto text-center">
                <h1 id="typewriter" class="text-4xl font-bold text-gray-100 mb-4 animate-fade-in-up min-h-14">
                    Welcome to My Portfolio
                </h1>
                <p class="text-lg text-gray-300 mb-8 animate-fade-in-up" style="animation-delay: 0.1s;">
                    Building modern web experiences with <span class="font-bold text-cyan-400">Laravel</span>, <span class="font-bold text-cyan-400">React</span>, and innovative solutions.
                </p>

                <!-- Profile Card -->
                <div class="bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/30 border border-slate-800 p-8 mb-12 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="w-32 h-32 rounded-full mx-auto mb-6 border-4 border-blue-600 shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-100 mb-2">Hi, I'm Francisco!</h2>
                    <p class="text-gray-300 mb-6">
                        A passionate web developer crafting digital experiences with clean code and creative design.
                    </p>
                    <div class="flex gap-3 flex-col">
                        <a href="#projects" class="bg-gradient-to-r from-indigo-600 via-cyan-600 to-blue-600 hover:from-indigo-700 hover:via-cyan-700 hover:to-blue-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-900/40 hover:shadow-indigo-900/60 transition-all duration-200 w-full">
                            View My Work
                        </a>
                        <a href="#contact" class="bg-slate-800 hover:bg-slate-700 text-gray-100 font-semibold py-3 rounded-xl transition-colors w-full">
                            Get In Touch
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack Section -->
        <section class="py-12 px-4 bg-slate-900/50">
            <div class="max-w-2xl md:max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-100 mb-8 text-center">Tech Stack</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg" alt="Laravel" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">Laravel</span>
                    </div>
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" alt="React" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">React</span>
                    </div>
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg" alt="Tailwind" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">Tailwind</span>
                    </div>
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">PHP</span>
                    </div>
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="MySQL" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">MySQL</span>
                    </div>
                    <div class="group flex flex-col items-center p-4 bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-700">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript" class="w-10 h-10 mb-2 group-hover:animate-bounce">
                        <span class="text-sm font-semibold text-gray-300">JavaScript</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section py-12 px-4">
            <div class="max-w-2xl md:max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-100 mb-8 text-center">About Me</h2>
                <div class="bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/20 border border-slate-800 p-6 mb-6">
                    <h3 class="text-xl font-semibold text-gray-100 mb-3">Who I Am</h3>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        I'm a passionate web developer with expertise in building modern, scalable applications. I love turning complex problems into simple, beautiful, and intuitive solutions.
                    </p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-slate-800 rounded-xl p-4 text-center border border-slate-700">
                        <div class="text-2xl font-bold text-cyan-400">5+</div>
                        <div class="text-xs text-gray-400 mt-1">Years Experience</div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-4 text-center border border-slate-700">
                        <div class="text-2xl font-bold text-cyan-400">{{ $projects->count() ?? 12 }}</div>
                        <div class="text-xs text-gray-400 mt-1">Projects</div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-4 text-center border border-slate-700">
                        <div class="text-2xl font-bold text-cyan-400">100%</div>
                        <div class="text-xs text-gray-400 mt-1">Dedication</div>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/20 border border-slate-800 p-6">
                    <h3 class="text-xl font-semibold text-gray-100 mb-3">Skills</h3>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-300">Laravel</span>
                                <span class="text-xs text-gray-400">90%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-600 to-blue-600" style="width: 90%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-300">React</span>
                                <span class="text-xs text-gray-400">85%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-600 to-blue-600" style="width: 85%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-300">UI/UX Design</span>
                                <span class="text-xs text-gray-400">80%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-600 to-blue-600" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="section py-12 px-4 bg-slate-900/50">
            <div class="max-w-2xl md:max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-100 mb-8 text-center">My Projects</h2>
                <div class="space-y-6 md:space-y-0 md:grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($projects as $index => $project)
                        <div class="group bg-slate-900 rounded-2xl overflow-hidden shadow-lg shadow-indigo-900/20 hover:shadow-2xl hover:shadow-indigo-900/40 border border-slate-800 transition-all duration-300 hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            <div class="aspect-video bg-slate-800 overflow-hidden">
                                <img loading="lazy" src="{{ $project->image_url }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-100 mb-2">{{ $project->title }}</h3>
                                <p class="text-gray-400 text-sm mb-4">{{ $project->description }}</p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach (array_slice($project->tags, 0, 3) as $tag)
                                        <span class="bg-indigo-900/30 text-cyan-400 text-xs font-semibold px-2 py-1 rounded-lg border border-blue-700/50">{{ trim($tag) }}</span>
                                    @endforeach
                                </div>
                                <a href="{{ route('projects.show', ['slug' => \Illuminate\Support\Str::slug($project->title)]) }}" class="inline-block w-full text-center bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold py-2 rounded-lg transition-all duration-200">
                                    View Project
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-8 text-center">
                            <p class="text-gray-400">More projects coming soon!</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('projects') }}" class="inline-block text-cyan-400 font-semibold hover:text-cyan-300">View all projects →</a>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="section py-12 px-4">
            <div class="max-w-2xl md:max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-100 mb-8 text-center">Let's Connect</h2>

                <!-- Contact Cards -->
                <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-3 gap-4 mb-8">
                    <a href="mailto:franciscomfcavaco@gmail.com" class="flex items-center p-4 bg-slate-900 rounded-xl border border-slate-800 hover:border-blue-600 hover:shadow-lg hover:shadow-indigo-900/30 transition-all duration-300">
                        <div class="w-12 h-12 bg-indigo-900/30 text-cyan-400 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Email</p>
                            <p class="text-gray-100 font-semibold truncate">franciscomfcavaco@gmail.com</p>
                        </div>
                        <i class="fas fa-arrow-right text-gray-500 ml-2"></i>
                    </a>

                    <a href="tel:+351924490605" class="flex items-center p-4 bg-slate-900 rounded-xl border border-slate-800 hover:border-blue-600 hover:shadow-lg hover:shadow-indigo-900/30 transition-all duration-300">
                        <div class="w-12 h-12 bg-indigo-900/30 text-cyan-400 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Phone</p>
                            <p class="text-gray-100 font-semibold">+351 924 490 605</p>
                        </div>
                        <i class="fas fa-arrow-right text-gray-500 ml-2"></i>
                    </a>

                    <div class="flex items-center p-4 bg-slate-900 rounded-xl border border-slate-800">
                        <div class="w-12 h-12 bg-indigo-900/30 text-cyan-400 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Location</p>
                            <p class="text-gray-100 font-semibold">Portugal</p>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
                    <h3 class="font-semibold text-gray-100 mb-4">Follow Me</h3>
                    <div class="flex gap-3">
                        <a href="https://www.linkedin.com/in/francisco-cavaco-dev" target="_blank" class="flex-1 py-3 bg-slate-800 rounded-lg text-center text-gray-300 hover:text-cyan-400 font-semibold transition-colors hover:bg-slate-700">
                            <i class="fab fa-linkedin mr-2"></i>LinkedIn
                        </a>
                        <a href="https://github.com" target="_blank" class="flex-1 py-3 bg-slate-800 rounded-lg text-center text-gray-300 hover:text-cyan-400 font-semibold transition-colors hover:bg-slate-700">
                            <i class="fab fa-github mr-2"></i>GitHub
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        @include('partials.footer')
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });

            // Particles animation
            const canvas = document.getElementById('particles-canvas');
            const ctx = canvas.getContext('2d');
            let particles = [];

            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }

            function createParticles() {
                particles = [];
                for (let i = 0; i < 30; i++) {
                    particles.push({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height,
                        vx: (Math.random() - 0.5) * 0.3,
                        vy: (Math.random() - 0.5) * 0.3,
                        size: Math.random() * 1.5 + 0.5,
                        opacity: Math.random() * 0.3 + 0.1
                    });
                }
            }

            function updateParticles() {
                particles.forEach(particle => {
                    particle.x += particle.vx;
                    particle.y += particle.vy;

                    if (particle.x < 0 || particle.x > canvas.width) particle.vx *= -1;
                    if (particle.y < 0 || particle.y > canvas.height) particle.vy *= -1;
                });
            }

            function drawParticles() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(particle => {
                    ctx.beginPath();
                    ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(99, 102, 241, ${particle.opacity})`;
                    ctx.fill();
                });
            }

            function animateParticles() {
                updateParticles();
                drawParticles();
                requestAnimationFrame(animateParticles);
            }

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
            createParticles();
            animateParticles();

            // Mobile menu toggle
            const menuToggle = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');
            
            if (menuToggle && menu) {
                menuToggle.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });

                // Close menu when a link is clicked
                menu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        menu.classList.add('hidden');
                    });
                });
            }
        });
    </script>
</body>
</html>
