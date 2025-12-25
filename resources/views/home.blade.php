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
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="flex flex-col items-center justify-center py-10 md:py-16 px-4">
            <div class="mb-8 md:mb-10 flex flex-col items-center text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-400 animate-fade-in-down mb-4 drop-shadow-lg">
                Welcome to My World!
            </h1>
            <p class="text-lg md:text-2xl text-gray-700 animate-fade-in-up text-center max-w-2xl">
                Building accessible, modern web experiences with <span class="font-semibold text-indigo-600">Laravel</span>, <span class="font-semibold text-cyan-600">React</span>, and more.
            </p>
        </div>

        <section class="glass rounded-3xl p-6 md:p-10 max-w-xl w-full text-center shadow-2xl border border-indigo-100 animate-fade-in">
            <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="mx-auto mb-6 w-56 h-56 rounded-full border-4 border-indigo-400 glow transition duration-500 hover:scale-105 hover:shadow-indigo-500/50">
            <h2 class="text-4xl font-extrabold text-indigo-700 mb-2 tracking-tight drop-shadow-lg">Welcome!</h2>
            <p class="text-lg text-gray-800 mb-6 leading-relaxed">
                Hello, I'm <span class="font-semibold text-indigo-600">Francisco Cavaco</span>.<br>
                I'm a web developer passionate about <span class="font-semibold">Laravel</span> and modern web technologies.<br>
                Explore my projects or learn more about me!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6 mb-4">
                <a href="{{ route('projects') }}" class="bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 hover:from-indigo-600 hover:via-cyan-600 hover:to-emerald-600 text-white px-8 py-3 rounded-xl font-semibold text-lg shadow-lg shadow-indigo-400/50 hover:-translate-y-1 transition duration-300 relative z-10">View Projects</a>
                <a href="{{ route('about') }}" class="px-8 py-3 bg-white border border-indigo-600 text-indigo-600 rounded-xl shadow-lg font-semibold text-lg hover:bg-indigo-50 transition z-10">About Me</a>
            </div>
        </section>

        <div class="w-11/12 md:w-2/3 mx-auto my-10 h-1 rounded-full bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 animate-pulse"></div>

        <div class="flex flex-col items-center">
            <h3 class="text-2xl font-bold text-indigo-700 mb-8">My Tech Stack</h3>
            <div class="flex flex-wrap justify-center gap-8">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" alt="HTML5" class="w-16 h-16 animate-float">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" alt="CSS3" class="w-16 h-16 animate-float delay-100">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript" class="w-16 h-16 animate-float delay-200">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP" class="w-16 h-16 animate-float delay-300">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg" alt="Laravel" class="w-16 h-16 animate-float delay-400">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" alt="React" class="w-16 h-16 animate-float delay-500">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="MySQL" class="w-16 h-16 animate-float delay-600">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg" alt="Tailwind CSS" class="w-16 h-16 animate-float delay-700">
            </div>
        </div>

        <div class="mt-12 flex justify-center">
            <div class="relative bg-white/80 backdrop-blur-lg rounded-2xl shadow-2xl border-4 border-transparent 
                bg-clip-padding p-8 max-w-md w-full transition-transform duration-300 hover:scale-105 
                hover:border-gradient-to-r hover:from-indigo-500 hover:via-cyan-400 hover:to-emerald-400">
                <div class="flex items-center justify-center mb-4">
                    <span class="text-4xl animate-bounce">💡</span>
                </div>
                <h4 class="text-2xl font-extrabold text-indigo-700 text-center mb-2">Fun Fact</h4>
                <p class="text-lg text-indigo-600 text-center font-semibold">
                    I built my first website at age <span class="text-cyan-500 font-bold">14</span>!
                </p>
            </div>
        </div>
    </main>

    <footer class="text-center py-6 bg-gradient-to-r from-indigo-700 via-purple-600 to-pink-500 text-white mt-16 rounded-t-xl shadow-lg animate-fade-in">
        <p>&copy; {{ date('Y') }} Francisco Cavaco. All rights reserved.</p>
    </footer>
    <script>
        // Fade-in animation for main section and footer
        document.querySelectorAll('.animate-fade-in').forEach(el => {
            setTimeout(() => {
                el.style.opacity = 1;
            }, 100);
        });
    </script>
</body>
</html>