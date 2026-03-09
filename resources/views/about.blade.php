<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-950 min-h-screen font-sans overflow-x-hidden">
    <!-- Particles Canvas -->
    <canvas id="particles-canvas" class="fixed inset-0 pointer-events-none z-10"></canvas>

    <!-- Floating Elements -->
    <div class="fixed inset-0 pointer-events-none z-5">
        <div class="absolute top-20 left-10 w-20 h-20 bg-cyan-400/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-32 h-32 bg-purple-400/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 left-1/4 w-24 h-24 bg-blue-400/10 rounded-full blur-xl animate-pulse" style="animation-delay: 4s;"></div>
    </div>
    
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-10 md:py-16 relative z-20">
        <!-- Hero Section -->
        <div class="text-center mb-12 md:mb-16">
            <div class="inline-block mb-6">
                <h1 id="about-typewriter" class="text-5xl md:text-7xl font-bold text-gray-100 mb-4 drop-shadow-lg animate-fade-in-up">
                    About Me
                </h1>
                <div class="w-full h-1 bg-gradient-to-r from-indigo-600 via-cyan-400 to-blue-600 rounded-full animate-fade-in-up" style="animation-delay: 0.5s;"></div>
            </div>
            <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto animate-fade-in-up font-medium leading-relaxed" style="animation-delay: 1s;">
                Passionate web developer crafting elegant digital experiences with modern technologies and creative problem-solving.
            </p>
        </div>
        <!-- Profile Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <!-- Profile Card -->
            <div class="bg-slate-900/60 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-800 p-8 md:p-10 animate-fade-in-left">
                <div class="text-center mb-8">
                    <div class="relative inline-block mb-6">
                        <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="w-48 h-48 rounded-full border-4 border-blue-600 shadow-2xl transition duration-500 hover:scale-105 hover:shadow-cyan-400/50">
                        <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-code text-white text-lg"></i>
                        </div>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-cyan-400 to-blue-600 mb-3 drop-shadow-lg">
                        Francisco Cavaco
                    </h2>
                    <div class="inline-block bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 text-white px-6 py-2 rounded-full text-lg font-semibold shadow-lg animate-pulse">
                        Web Developer & CS Student
                    </div>
                </div>

                <div class="space-y-4 text-gray-300">
                    <p class="text-lg leading-relaxed">
                        I am a Computer Science Engineering student at Instituto Piaget, having completed a Higher Professional Technical Course (CTeSP) in Game Development and Multimedia Applications. I have solid experience in web development, particularly in frontend development and digital accessibility, and I am passionate about exploring new technologies and trends in the field.
                    </p>

                    <div id="extended-bio" class="hidden">
                        <p class="text-lg leading-relaxed">
                            Previously, I completed the Technical Course in Management and Programming of Computer Systems at EPED, where I gained skills in multiple programming languages, including C#, HTML, JavaScript, CSS, Bootstrap, and PHP. As part of this program, I completed a 600-hour internship as a Frontend Developer, where I built accessible web pages for users with visual and motor impairments, ensuring an inclusive browsing experience.
                            <br><br>
                        </p>
                        <p class="text-lg leading-relaxed">
                            More recently, I carried out an internship at Mindshaker as a Web Developer (starting on March 17, 2025), where I received hands-on training in HTML, CSS, JavaScript, Laravel, and React, and contributed to several projects — including an e-commerce website, a Laravel-based blog, a React to-do list application, and a job-search platform. I gained practical experience with Tailwind CSS, Laravel Blade, Laravel Breeze, MySQL/phpMyAdmin, and React.
                        </p>
                        <p class="text-lg leading-relaxed">
                            Beyond my academic path, I participated in the "Unlimited Tech Powered by Worten" program and joined the Piaget Management Multimedia Department, where I contributed to website development and the organization of an annual management and technology event.
                        </p>
                    </div>

                    <button id="read-more-btn" class="w-full text-cyan-400 font-bold mt-2 mb-6 flex items-center justify-center gap-2 hover:text-cyan-300 transition group">
                        <span>Read More</span>
                        <svg class="w-4 h-4 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="space-y-6 animate-fade-in-right">
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 border border-slate-800 shadow-lg">
                    <h3 class="text-2xl font-bold text-cyan-400 mb-4 text-center">Quick Stats</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-slate-800/50 rounded-xl">
                            <div class="text-3xl font-bold text-cyan-400 mb-1">{{ now()->year - 2020 }}</div>
                            <div class="text-sm text-gray-400">Years Learning</div>
                        </div>
                        <div class="text-center p-4 bg-slate-800/50 rounded-xl">
                            <div class="text-3xl font-bold text-cyan-400 mb-1">3+</div>
                            <div class="text-sm text-gray-400">Projects</div>
                        </div>
                        <div class="text-center p-4 bg-slate-800/50 rounded-xl">
                            <div class="text-3xl font-bold text-cyan-400 mb-1">9+</div>
                            <div class="text-sm text-gray-400">Technologies</div>
                        </div>
                        <div class="text-center p-4 bg-slate-800/50 rounded-xl">
                            <div class="text-3xl font-bold text-cyan-400 mb-1">2</div>
                            <div class="text-sm text-gray-400">Internships</div>
                        </div>
                    </div>
                </div>

                <!-- Personal Qualities -->
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 border border-slate-800 shadow-lg">
                    <h3 class="text-2xl font-bold text-cyan-400 mb-4 text-center">Personal Qualities</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                            <i class="fas fa-comments text-cyan-400 text-lg"></i>
                            <span class="text-gray-300 font-medium">Communicative</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                            <i class="fas fa-clock text-cyan-400 text-lg"></i>
                            <span class="text-gray-300 font-medium">Punctual</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                            <i class="fas fa-star text-cyan-400 text-lg"></i>
                            <span class="text-gray-300 font-medium">Dedicated</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                            <i class="fas fa-users text-cyan-400 text-lg"></i>
                            <span class="text-gray-300 font-medium">Team Player</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills Section -->
        <div class="mb-16 animate-fade-in-up">
            <h2 class="text-4xl font-bold text-center text-gray-100 mb-12">Technical Skills</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <!-- Frontend Skills -->
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-html5 text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">HTML5</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-orange-400 to-red-400 h-2 rounded-full" style="width: 95%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Expert</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-css3-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">CSS3</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" style="width: 90%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Expert</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-js-square text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">JavaScript</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Advanced</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-react text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">React</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-cyan-400 to-blue-500 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Advanced</span>
                </div>

                <!-- Backend Skills -->
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-php text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">PHP</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Advanced</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-laravel text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">Laravel</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Advanced</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-database text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">MySQL</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-700 h-2 rounded-full" style="width: 75%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Intermediate</span>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl rounded-2xl p-6 text-center shadow-lg border border-slate-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-cuttlefish text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-100 mb-2">C#</h3>
                    <div class="w-full bg-slate-700 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-indigo-400 to-blue-600 h-2 rounded-full" style="width: 70%"></div>
                    </div>
                    <span class="text-sm text-gray-400">Intermediate</span>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center animate-fade-in-up">
            <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 rounded-3xl p-8 md:p-12 shadow-2xl text-white max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Let’s build something together</h2>
                <p class="text-lg md:text-xl mb-8 opacity-90">
                    I design and develop accessible websites and web applications with clean, maintainable code.
                    Have a project in mind? Feel free to get in touch.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-3 bg-slate-900 text-cyan-400 font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <i class="fas fa-envelope text-lg"></i>
                        Get In Touch
                    </a>
                    <a href="{{ route('projects') }}" class="inline-flex items-center gap-3 bg-blue-700 backdrop-blur-sm text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-blue-500">
                        <i class="fas fa-code text-lg"></i>
                        View My Work
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="fixed bottom-8 right-8 bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 translate-y-4 z-40 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @include('partials.footer')

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/about.js'])
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>
