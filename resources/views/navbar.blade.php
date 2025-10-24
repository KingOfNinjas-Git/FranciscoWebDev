<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Francisco WebDev')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="fixed top-0 left-0 w-full z-50 bg-white/60 backdrop-blur-lg shadow-lg border-b border-indigo-200">
        <div class="container mx-auto flex items-center justify-between py-4 px-6">
            <a href="{{ route('home') }}" 
               class="text-2xl font-extrabold tracking-wide select-none"
               style="background: linear-gradient(90deg, #6366f1 0%, #06b6d4 100%);
                      background-clip: text;
                      -webkit-background-clip: text;
                      color: transparent;
                      -webkit-text-fill-color: transparent;
                      text-shadow: 0 2px 12px #6366f1;">
                Francisco WebDev
            </a>
            <nav>
                <ul class="flex space-x-8">
                    <li>
                        <a href="{{ route('home') }}" class="relative text-lg font-semibold px-2 py-1 text-indigo-700 hover:text-cyan-500 transition duration-300
                            after:absolute after:left-1/2 after:-bottom-1 after:w-4/5 after:h-1 after:bg-gradient-to-r after:from-indigo-500 after:to-cyan-400 after:rounded-full after:transform after:-translate-x-1/2 after:scale-x-0 hover:after:scale-x-100 after:transition-all after:duration-300">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('about') }}" class="relative text-lg font-semibold px-2 py-1 text-indigo-700 hover:text-cyan-500 transition duration-300
                            after:absolute after:left-1/2 after:-bottom-1 after:w-4/5 after:h-1 after:bg-gradient-to-r after:from-indigo-500 after:to-cyan-400 after:rounded-full after:transform after:-translate-x-1/2 after:scale-x-0 hover:after:scale-x-100 after:transition-all after:duration-300">
                            About
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects') }}" class="relative text-lg font-semibold px-2 py-1 text-indigo-700 hover:text-cyan-500 transition duration-300
                            after:absolute after:left-1/2 after:-bottom-1 after:w-4/5 after:h-1 after:bg-gradient-to-r after:from-indigo-500 after:to-cyan-400 after:rounded-full after:transform after:-translate-x-1/2 after:scale-x-0 hover:after:scale-x-100 after:transition-all after:duration-300">
                            Projects
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="h-20"></div> <!-- Spacer for fixed navbar -->
    <main>
        @yield('content')
    </main>
</body>
</html>