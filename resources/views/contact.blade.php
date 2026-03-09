<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Comic Neue', cursive; }

        /* Form animations */
        .form-group {
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Input focus effects */
        input:focus, textarea:focus, select:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
        }

        /* Button hover effects */
        button:hover {
            transform: translateY(-2px) scale(1.02);
        }

        /* Social link hover effects */
        .social-link {
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
        }

        /* Contact card hover effects */
        .contact-card {
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px) scale(1.02);
        }
    </style>
</head>
<body class="bg-slate-950 min-h-screen font-sans flex flex-col">
    <!-- Particles Canvas -->
    <canvas id="particles-canvas" class="fixed inset-0 pointer-events-none z-10"></canvas>
    
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-12 flex-grow">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 id="contact-typewriter" class="text-5xl md:text-7xl font-bold text-gray-100 mb-6 drop-shadow-lg">
                Let's Connect
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto animate-fade-in-up font-medium">
                Ready to bring your ideas to life? I'd love to hear about your project and discuss how we can work together.
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Contact Info & Social -->
            <div class="space-y-8 animate-fade-in-right">
                <!-- Contact Cards -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Email -->
                    <a href="mailto:franciscomfcavaco@gmail.com" class="group flex items-center p-6 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-2xl transition-all duration-300 hover:shadow-lg hover:shadow-indigo-900/30 hover:-translate-y-1 contact-card">
                        <div class="w-14 h-14 bg-slate-800 text-cyan-400 rounded-full flex items-center justify-center mr-6 group-hover:bg-blue-600 group-hover:text-white transition-colors flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Email</p>
                            <p class="text-lg font-comic text-gray-100 font-bold break-all">franciscomfcavaco@gmail.com</p>
                            <p class="text-sm text-gray-500 mt-1">Response within 24 hours</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>

                    <!-- Phone -->
                    <a href="tel:+351924490605" class="group flex items-center p-6 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-2xl transition-all duration-300 hover:shadow-lg hover:shadow-indigo-900/30 hover:-translate-y-1 contact-card">
                        <div class="w-14 h-14 bg-slate-800 text-cyan-400 rounded-full flex items-center justify-center mr-6 group-hover:bg-blue-600 group-hover:text-white transition-colors flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Phone</p>
                            <p class="text-lg font-comic text-gray-100 font-bold">+351 924 490 605</p>
                            <p class="text-sm text-gray-500 mt-1">Available for calls</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>

                    <!-- Location -->
                    <div class="flex items-center p-6 bg-slate-900 border border-slate-800 rounded-2xl contact-card">
                        <div class="w-14 h-14 bg-slate-800 text-cyan-400 rounded-full flex items-center justify-center mr-6 flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Location</p>
                            <p class="text-lg font-comic text-gray-100 font-bold">Portugal</p>
                            <p class="text-sm text-gray-500 mt-1">Available for remote work worldwide</p>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="bg-slate-900 backdrop-blur-xl rounded-2xl p-6 border border-slate-800">
                    <h3 class="text-xl font-bold text-cyan-400 mb-4">Follow Me</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="https://www.linkedin.com/in/francisco-cavaco-dev" target="_blank" class="flex items-center gap-3 p-3 bg-slate-800 hover:bg-slate-700 rounded-xl transition-colors social-link group">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-100">LinkedIn</p>
                                <p class="text-sm text-gray-400">Professional Network</p>
                            </div>
                        </a>

                        <a href="https://github.com/KingOfNinjas-Git" target="_blank" class="flex items-center gap-3 p-3 bg-slate-800 hover:bg-slate-700 rounded-xl transition-colors social-link group">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-100">GitHub</p>
                                <p class="text-sm text-gray-400">Code Portfolio</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Availability Status -->
                <div class="bg-slate-900 backdrop-blur-xl rounded-2xl p-6 border border-blue-600/50">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
                        <h3 class="text-lg font-bold text-cyan-400">Available for Projects</h3>
                    </div>
                    <p class="text-cyan-400/80 mb-3">Currently accepting new projects and collaborations.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-slate-800 text-cyan-400 rounded-full text-sm font-medium border border-slate-700">Web Development</span>
                        <span class="px-3 py-1 bg-slate-800 text-cyan-400 rounded-full text-sm font-medium border border-slate-700">Laravel</span>
                        <span class="px-3 py-1 bg-slate-800 text-cyan-400 rounded-full text-sm font-medium border border-slate-700">React</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Projects -->
        <div class="text-center mt-12">
            <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 font-bold transition-colors group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Projects
            </a>
        </div>
    </main>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="fixed bottom-8 right-8 bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 translate-y-4 z-40 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/contact.js'])

    @include('partials.footer')
</body>
</html>