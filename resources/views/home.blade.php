<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Francisco Cavaco | Web Developer</title>
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 min-h-screen font-sans">
    <!-- Particles Canvas -->
    <canvas id="particles-canvas" class="fixed inset-0 pointer-events-none z-10"></canvas>
    
    @include('navbar')

    <main class="flex flex-col items-center justify-center py-12 md:py-20 px-4">
        <div class="mb-12 md:mb-16 flex flex-col items-center text-center max-w-2xl">
            <h1 id="typewriter" class="text-5xl md:text-7xl font-bold text-gray-100 mb-4 drop-shadow-lg animate-fade-in-up">
                Welcome to My Portfolio
            </h1>
            <p class="text-lg md:text-xl text-gray-300 animate-fade-in-up text-center leading-relaxed font-medium" style="animation-delay: 0.1s;">
                Crafting modern web experiences with <span class="font-bold text-cyan-400">Laravel</span>, <span class="font-bold text-cyan-400">React</span>, and innovative solutions.
            </p>
        </div>

        <section class="bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/20 border border-slate-800 p-8 md:p-12 max-w-xl w-full text-center mb-12 animate-fade-in" style="animation-delay: 0.2s;">
            <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="mx-auto mb-8 w-56 h-56 rounded-full border-4 border-blue-600 shadow-lg transition duration-500 hover:scale-105 hover:shadow-cyan-400/50">
            <h2 class="text-3xl font-bold text-gray-100 mb-3 tracking-tight">Hi, I'm Francisco!</h2>
            <p class="text-lg text-gray-300 mb-8 leading-relaxed font-medium">
                I'm a passionate web developer who turns complex problems into elegant, functional solutions. Let's build something amazing together.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-4">
                <a href="{{ route('projects') }}" class="bg-gradient-to-r from-indigo-600 via-cyan-600 to-blue-600 hover:from-indigo-700 hover:via-cyan-700 hover:to-blue-700 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-lg shadow-indigo-900/40 hover:shadow-indigo-900/60 hover:-translate-y-0.5 transition duration-200 relative z-10">View Projects</a>
                <a href="{{ route('about') }}" class="px-8 py-3 bg-slate-800 border border-slate-700 text-gray-100 rounded-lg shadow-md font-semibold text-lg hover:bg-slate-700 transition z-10">About Me</a>
            </div>
        </section>

        <div class="w-full md:w-2/3 mx-auto mb-12 h-0.5 rounded-full bg-gradient-to-r from-indigo-600 via-cyan-600 to-blue-600 opacity-50"></div>

        <div class="flex flex-col items-center w-full">
            <h3 class="text-3xl font-bold text-gray-100 mb-10">Tech Stack</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 max-w-5xl">
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" alt="HTML5" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">HTML5</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" alt="CSS3" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">CSS3</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">JavaScript</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">PHP</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg" alt="Laravel" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">Laravel</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" alt="React" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">React</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="MySQL" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">MySQL</span>
                </div>
                <div class="group flex flex-col items-center p-4 bg-slate-900 rounded-xl shadow-md hover:shadow-lg border border-slate-800 hover:border-blue-600 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg" alt="Tailwind CSS" class="w-12 h-12 mb-2 group-hover:animate-bounce">
                    <span class="text-xs font-semibold text-gray-300">Tailwind</span>
                </div>
            </div>
        </div>

        <div class="mt-16 flex justify-center">
            <div class="relative bg-slate-900 rounded-2xl shadow-lg shadow-indigo-900/20 border border-slate-800 p-8 max-w-md w-full transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                <div class="flex items-center justify-center mb-4">
                    <span class="text-4xl animate-bounce">💡</span>
                </div>
                <h4 class="text-2xl font-bold text-gray-100 text-center mb-2">Fun Fact</h4>
                <p class="text-lg text-gray-300 text-center font-semibold">
                    I built my first website at age <span class="text-cyan-400 font-bold">14</span>!
                </p>
            </div>
        </div>
    </main>

    @include('partials.footer')
    <button id="scroll-to-top" class="fixed bottom-6 right-6 w-12 h-12 bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 transform translate-y-4 hover:scale-110 z-40">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>
    @vite(['resources/js/common.js', 'resources/js/navbar.js', 'resources/js/home.js'])
</body>
</html>