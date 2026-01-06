<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Francisco Cavaco</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-400 via-cyan-300 to-purple-300 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Title Card -->
        <div class="text-center mb-8 animate-fade-in">
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-cyan-600 to-pink-600">Francisco</h1>
            <p class="text-indigo-700 mt-2 text-sm font-medium">Admin Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-gradient-to-b from-white/95 via-indigo-50/90 to-cyan-50/90 rounded-2xl shadow-2xl shadow-indigo-300/40 border border-white/80 p-8 backdrop-blur-sm">
            <h2 class="text-2xl font-extrabold text-indigo-900 mb-6">Secure Access</h2>

            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-800 rounded-lg text-sm font-medium">
                    <span>⚠ {{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-indigo-900 mb-2">Admin Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white/80 text-gray-900 placeholder-gray-500 transition" placeholder="Enter password" required autofocus>
                </div>

                <button class="w-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 hover:from-indigo-600 hover:via-cyan-600 hover:to-emerald-600 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-400/40 hover:shadow-indigo-400/60 hover:-translate-y-0.5 transition duration-200">
                    Log In
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-indigo-700/70 text-xs mt-6 font-medium">v2.0 Admin</p>
    </div>
</body>
</html>