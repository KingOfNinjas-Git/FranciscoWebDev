<header class="fixed top-0 left-0 right-0 z-50 bg-slate-950/95 backdrop-blur-xl border-b border-slate-800">
    <div class="max-w-6xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-lg group-hover:scale-110 transition-transform duration-300">FC</div>
                <span class="text-xl font-bold text-gray-100 group-hover:text-indigo-400 transition-colors">Francisco</span>
            </a>
            
            <!-- Desktop Menu -->
            <nav class="hidden md:block">
                <ul class="flex items-center gap-12">
                    <li>
                        <a href="{{ route('home') }}" class="relative text-sm font-semibold text-gray-300 hover:text-indigo-400 transition-colors duration-300 group py-2">
                            Home
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('about') }}" class="relative text-sm font-semibold text-gray-300 hover:text-indigo-400 transition-colors duration-300 group py-2">
                            About
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects') }}" class="relative text-sm font-semibold text-gray-300 hover:text-indigo-400 transition-colors duration-300 group py-2">
                            Projects
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="relative text-sm font-semibold text-gray-300 hover:text-indigo-400 transition-colors duration-300 group py-2">
                            Contact
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.projects.index') }}" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-600 hover:from-indigo-700 hover:via-blue-700 hover:to-blue-700 text-white text-sm font-semibold shadow-lg shadow-indigo-900/40 hover:shadow-indigo-900/60 hover:-translate-y-0.5 transition-all duration-200">Admin</a>
                            Admin
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-indigo-400 transition-colors focus:outline-none p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="md:hidden overflow-hidden transition-all duration-500 ease-in-out max-h-0 opacity-0 border-t border-slate-700 mt-4 pt-4">
            <ul class="flex flex-col gap-3 pb-4">
                <li><a href="{{ route('home') }}" class="block text-gray-300 font-semibold hover:text-indigo-400 hover:bg-slate-800 rounded-lg py-2 px-3 transition-colors">Home</a></li>
                <li><a href="{{ route('about') }}" class="block text-gray-300 font-semibold hover:text-indigo-400 hover:bg-slate-800 rounded-lg py-2 px-3 transition-colors">About</a></li>
                <li><a href="{{ route('projects') }}" class="block text-gray-300 font-semibold hover:text-indigo-400 hover:bg-slate-800 rounded-lg py-2 px-3 transition-colors">Projects</a></li>
                <li><a href="{{ route('contact') }}" class="block text-gray-300 font-semibold hover:text-indigo-400 hover:bg-slate-800 rounded-lg py-2 px-3 transition-colors">Contact</a></li>
                <li><a href="{{ route('admin.projects.index') }}" class="block px-3 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold text-sm mt-2 hover:from-indigo-700 hover:to-blue-700 transition-all">Admin</a></li>
            </ul>
        </div>
    </div>
</header>
<!-- Spacer for fixed navbar -->
<div class="md:h-20"></div>
@include('partials.lang_splash')
