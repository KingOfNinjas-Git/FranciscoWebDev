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
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans flex flex-col">
    @include('navbar')

    <main class="container mx-auto px-4 md:px-6 py-12 flex-grow flex items-center justify-center">
        <div class="max-w-2xl w-full bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/60 p-8 md:p-12 animate-fade-in-up">
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 mb-4 drop-shadow-sm">Get in Touch</h1>
                <p class="text-xl text-indigo-900 font-comic font-medium">I'd love to hear from you!</p>
            </div>

            <div class="space-y-6">
                <!-- Email -->
                <a href="mailto:franciscomfcavaco@gmail.com" class="flex items-center p-4 bg-white/60 hover:bg-white border border-indigo-100 rounded-2xl transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Email</p>
                        <p class="text-lg md:text-xl font-comic text-indigo-900 font-bold break-all">franciscomfcavaco@gmail.com</p>
                    </div>
                </a>

                <!-- Phone -->
                <a href="tel:+351924490605" class="flex items-center p-4 bg-white/60 hover:bg-white border border-indigo-100 rounded-2xl transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Phone</p>
                        <p class="text-lg md:text-xl font-comic text-indigo-900 font-bold">+351 924490605</p>
                    </div>
                </a>

                <!-- LinkedIn -->
                <a href="https://www.linkedin.com/in/francisco-cavaco-dev" target="_blank" class="flex items-center p-4 bg-white/60 hover:bg-white border border-indigo-100 rounded-2xl transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">LinkedIn</p>
                        <p class="text-lg md:text-xl font-comic text-indigo-900 font-bold break-all">www.linkedin.com/in/francisco-cavaco-dev</p>
                    </div>
                </a>
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-bold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Projects
                </a>
            </div>
        </div>
    </main>
</body>
</html>