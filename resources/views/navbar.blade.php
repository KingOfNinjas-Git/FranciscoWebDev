<header class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-11/12 lg:w-3/4 xl:w-2/3">
    <div class="bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg shadow-indigo-200/40 border border-white/20 p-3 transition-all duration-300">
        <div class="container mx-auto flex items-center justify-between px-2">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center text-white font-extrabold shadow-md">FC</div>
                <div class="text-lg font-extrabold tracking-wide text-indigo-700">Francisco</div>
            </a>
            
            <!-- Desktop Menu -->
            <nav class="hidden md:block">
                <ul class="flex items-center gap-6">
                    <li><a href="{{ route('home') }}" class="text-sm font-semibold text-muted hover:text-indigo-700 transition">Home</a></li>
                    <li><a href="{{ route('about') }}" class="text-sm font-semibold text-muted hover:text-indigo-700 transition">About</a></li>
                    <li><a href="{{ route('projects') }}" class="text-sm font-semibold text-muted hover:text-indigo-700 transition">Projects</a></li>
                    <li><a href="{{ route('admin.projects.index') }}" class="v2-pill">Admin</a></li>
                </ul>
            </nav>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-indigo-700 focus:outline-none p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 border-t border-gray-100 pt-4 pb-2">
            <ul class="flex flex-col gap-4 text-center">
                <li><a href="{{ route('home') }}" class="block text-muted font-semibold hover:text-indigo-600 py-2">Home</a></li>
                <li><a href="{{ route('about') }}" class="block text-muted font-semibold hover:text-indigo-600 py-2">About</a></li>
                <li><a href="{{ route('projects') }}" class="block text-muted font-semibold hover:text-indigo-600 py-2">Projects</a></li>
                <li><a href="{{ route('admin.projects.index') }}" class="inline-block v2-pill">Admin</a></li>
            </ul>
        </div>
    </div>
</header>
<!-- spacer for fixed header -->
<div class="h-20"></div>
@include('partials.lang_splash')

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>