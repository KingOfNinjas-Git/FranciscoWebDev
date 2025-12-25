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
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-200 to-purple-100 min-h-screen font-sans">
    @include('navbar')

    <main class="flex flex-col items-center justify-center py-10 md:py-16 px-4">
        <section class="bg-gradient-to-b from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl shadow-lg shadow-indigo-200/50 border border-white/80 p-6 md:p-10 max-w-2xl w-full animate-fade-in backdrop-blur-sm">
            <div class="flex flex-col items-center mb-8">
                <img src="{{ asset('imgs/Francisco.jpg') }}" alt="Francisco Cavaco" class="w-48 h-48 rounded-full border-4 border-indigo-400 glow transition duration-500 hover:scale-105 hover:shadow-indigo-500/50">
                <h2 class="text-3xl md:text-4xl font-extrabold text-indigo-700 mt-6 mb-2 tracking-tight drop-shadow-lg text-center">Francisco Cavaco</h2>
                <span class="inline-block bg-gradient-to-r from-indigo-500 via-purple-400 to-pink-400 text-white px-4 py-1 rounded-full text-base md:text-lg font-semibold shadow-lg animate-pulse text-center">Web Developer & CS Student</span>
            </div>
            <p class="text-gray-800 mb-4 text-lg leading-relaxed">
                I am a Computer Science Engineering student at Instituto Piaget, having completed a Higher Professional Technical Course (CTeSP) in Game Development and Multimedia Applications. I have solid experience in web development, particularly in frontend development and digital accessibility, and I am passionate about exploring new technologies and trends in the field.
            </p>

            <div id="extended-bio" class="hidden md:block">
                <p class="text-gray-800 mb-4 text-lg leading-relaxed">
                    Previously, I completed the Technical Course in Management and Programming of Computer Systems at EPED, where I gained skills in multiple programming languages, including C#, HTML, JavaScript, CSS, Bootstrap, and PHP. As part of this program, I completed a 600-hour internship as a Frontend Developer, where I built accessible web pages for users with visual and motor impairments, ensuring an inclusive browsing experience.
                </p>
                <p class="text-gray-800 mb-4 text-lg leading-relaxed">
                    More recently, I carried out an internship at Mindshaker as a Web Developer (starting on March 17, 2025), where I received hands-on training in HTML, CSS, JavaScript, Laravel, and React, and contributed to several projects — including an e-commerce website, a Laravel-based blog, a React to-do list application, and a job-search platform. I gained practical experience with Tailwind CSS, Laravel Blade, Laravel Breeze, MySQL/phpMyAdmin, and React.
                </p>
                <p class="text-gray-800 mb-4 text-lg leading-relaxed">
                    Beyond my academic path, I participated in the “Unlimited Tech Powered by Worten” program and joined the Piaget Management Multimedia Department, where I contributed to website development and the organization of an annual management and technology event.
                </p>
            </div>

            <button id="read-more-btn" class="md:hidden w-full text-indigo-600 font-bold mt-2 mb-6 flex items-center justify-center gap-2 hover:text-indigo-800 transition group">
                <span>Read More</span>
                <svg class="w-4 h-4 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <hr class="my-8 border-indigo-200">
            <h3 class="text-2xl font-bold text-indigo-700 mb-4 text-center">Skills</h3>
            <div class="flex flex-wrap gap-3 justify-center mb-8">
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">HTML</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">CSS</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">JavaScript</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">PHP</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">React</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">Laravel</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">MySQL</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">Tailwind CSS</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">Bootstrap</span>
                <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-base font-semibold shadow-md hover:bg-indigo-200 transition cursor-pointer skill-bounce">C#</span>
            </div>
            <hr class="my-8 border-indigo-200">
            <h3 class="text-2xl font-bold text-indigo-700 mb-4 text-center">Personal Qualities</h3>
            <ul class="list-disc list-inside text-gray-800 mb-6 text-lg">
                <li>Communicative</li>
                <li>Punctual</li>
                <li>Dedicated</li>
                <li>Strong teamwork & leadership skills</li>
            </ul>
            <p class="text-gray-800 text-lg text-center">
                My goal is to continue growing professionally in web development and to build a career in web development and software engineering.
            </p>
        </section>
    </main>

    <footer class="text-center py-6 bg-gradient-to-r from-indigo-700 via-purple-600 to-pink-500 text-white mt-16 rounded-t-xl shadow-lg animate-fade-in">
        <p>&copy; {{ date('Y') }} Francisco Cavaco. All rights reserved.</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            AOS && AOS.init && AOS.init({ once:true, duration:700 });
            document.querySelectorAll('.animate-fade-in').forEach(el => { el.style.opacity = 0; setTimeout(()=>{ el.style.transition = 'opacity 1.2s'; el.style.opacity = 1 }, 100)});
            
            // Read More Toggle Logic
            const btn = document.getElementById('read-more-btn');
            if(btn){
                btn.addEventListener('click', function(){
                    document.getElementById('extended-bio').classList.remove('hidden');
                    this.style.display = 'none';
                });
            }
        });
    </script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>