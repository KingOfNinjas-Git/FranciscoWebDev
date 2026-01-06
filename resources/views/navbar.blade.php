<header class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-11/12 lg:w-3/4 xl:w-2/3">
    <div class="bg-white/30 backdrop-blur-xl rounded-[2rem] shadow-2xl shadow-indigo-500/15 border border-white/40 p-2 px-6 transition-all duration-300 hover:shadow-indigo-500/25 hover:bg-white/60">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-black shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">FC</div>
                <span class="text-xl font-bold tracking-tight text-slate-800 group-hover:text-indigo-600 transition-colors">Francisco</span>
            </a>
            
            <!-- Desktop Menu -->
            <nav class="hidden md:block">
                <ul class="flex items-center gap-8">
                    <li>
                        <a href="{{ route('home') }}" class="relative text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-indigo-600 transition-colors duration-300 group py-2">
                            Home
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('about') }}" class="relative text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-indigo-600 transition-colors duration-300 group py-2">
                            About
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects') }}" class="relative text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-indigo-600 transition-colors duration-300 group py-2">
                            Projects
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="relative text-xs font-bold uppercase tracking-widest text-slate-600 hover:text-indigo-600 transition-colors duration-300 group py-2">
                            Contact
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full rounded-full"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.projects.index') }}" class="px-5 py-2 rounded-full bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-indigo-600 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 transform hover:-translate-y-0.5">
                            Admin
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-slate-700 hover:text-indigo-600 transition-colors focus:outline-none p-2">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="md:hidden overflow-hidden transition-all duration-500 ease-in-out max-h-0 opacity-0 border-t border-transparent">
            <ul class="flex flex-col gap-3 text-center py-4 px-2">
                <li><a href="{{ route('home') }}" class="block text-slate-600 font-bold hover:text-indigo-600 hover:bg-indigo-50 rounded-lg py-2 transition-colors">Home</a></li>
                <li><a href="{{ route('about') }}" class="block text-slate-600 font-bold hover:text-indigo-600 hover:bg-indigo-50 rounded-lg py-2 transition-colors">About</a></li>
                <li><a href="{{ route('projects') }}" class="block text-slate-600 font-bold hover:text-indigo-600 hover:bg-indigo-50 rounded-lg py-2 transition-colors">Projects</a></li>
                <li><a href="{{ route('contact') }}" class="block text-slate-600 font-bold hover:text-indigo-600 hover:bg-indigo-50 rounded-lg py-2 transition-colors">Contact</a></li>
                <li><a href="{{ route('admin.projects.index') }}" class="inline-block px-6 py-2 mt-2 rounded-full bg-slate-900 text-white font-bold text-sm hover:bg-indigo-600 transition-colors">Admin</a></li>
            </ul>
        </div>
    </div>
</header>
<!-- spacer for fixed header -->
<div class="h-20"></div>
@include('partials.lang_splash')

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        if (menu.classList.contains('max-h-0')) {
            menu.classList.remove('max-h-0', 'opacity-0', 'border-transparent');
            menu.classList.add('max-h-96', 'opacity-100', 'mt-4', 'border-slate-200/60');
        } else {
            menu.classList.add('max-h-0', 'opacity-0', 'border-transparent');
            menu.classList.remove('max-h-96', 'opacity-100', 'mt-4', 'border-slate-200/60');
        }
    });
</script>